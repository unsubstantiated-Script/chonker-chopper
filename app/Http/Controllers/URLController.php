<?php

namespace App\Http\Controllers;

use App\Models\URL;
use App\Models\URLAnalytics;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class URLController extends Controller
{
    /**
     * Display the upload form.
     *
     * @return View
     */
    public function create(): View
    {
        return view('urls.create');
    }

    /**
     * Store a new URL the newly generated shortened version.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {

        // Handle CSV file upload
        if (!$request->hasFile('csv_file')) {
            return redirect()->route('urls.upload')->with('error', 'Please upload a CSV file.');
        }

        $this->processCSVFile($request);

        return redirect()->route('urls.view')->with('success', 'URL shortened successfully!');
    }

    private function processCSVFile(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $csvData = array_map('str_getcsv', file($file->getPathname()));
        $urlsCreated = 0;

        foreach ($csvData as $row) {
            // Handle both formats: single URL or URL,description
            $url = is_array($row) ? trim($row[0]) : trim($row);

            // Skip empty rows or headers
            if (empty($url) || $url === 'url' || !filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }

            URL::create([
                'batch_id' => $this->getBatchId(),
                'original_url' => $url,
                'short_url' => $this->generateShortUrl(),
            ]);

            $urlsCreated++;
        }

        return redirect()->route('urls.view')->with('success', "Successfully shortened {$urlsCreated} URLs from CSV!");
    }


    /**
     * Display all URLs grouped by batch.
     *
     * @return View
     */
    public function index(): View
    {
        $urlBatches = URL::select('batch_id', 'created_at')
            ->groupBy('batch_id', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($batch) {
                return [
                    'batch_id' => $batch->batch_id,
                    'created_at' => $batch->created_at,
                    'urls' => URL::where('batch_id', $batch->batch_id)->get(),
                    'total_urls' => URL::where('batch_id', $batch->batch_id)->count()
                ];
            });

        return view('urls.index', compact('urlBatches'));
    }

    /**
     * Display analytics for all URLs grouped by batch.
     *
     * @return View
     */
    public function analytics(): View
    {
        $analyticsBatches = URL::select('batch_id', 'created_at')
            ->groupBy('batch_id', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($batch) {
                $urls = URL::with('analytics')->where('batch_id', $batch->batch_id)->get();
                $totalClicks = $urls->sum(function ($url) {
                    return $url->analytics->count();
                });

                return [
                    'batch_id' => $batch->batch_id,
                    'created_at' => $batch->created_at,
                    'urls' => $urls,
                    'total_urls' => $urls->count(),
                    'total_clicks' => $totalClicks
                ];
            });

        return view('urls.analytics', compact('analyticsBatches'));
    }

    /**
     * Get or create batch ID for the session.
     *
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getBatchId(): string
    {
        if (!session()->has('batch_id')) {
            session()->put('batch_id', Str::uuid()->toString());
        }

        return session()->get('batch_id');
    }

    /**
     * Generate a unique short URL.
     *
     * @return string
     */
    private function generateShortUrl(): string
    {
        do {
            $shortUrl = Str::random(6);
        } while (URL::where('short_url', $shortUrl)->exists());

        return $shortUrl;
    }

    /**
     * Redirect short URL to the original URL and track analytics.
     *
     * @param string $shortUrl
     * @param Request $request
     * @return RedirectResponse
     */
    public function redirect(string $shortUrl, Request $request): RedirectResponse
    {
        $url = URL::where('short_url', $shortUrl)->firstOrFail();

        // Track the click
        URLAnalytics::create([
            'url_id' => $url->id,
            'geographic_location' => $this->getLocationFromIP($request->ip()),
            'browser' => $this->getBrowserFromUserAgent($request->userAgent()),
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'referrer' => $request->headers->get('referer'),
            'clicked_at' => now(),
        ]);

        return redirect($url->original_url);
    }

    /**
     * Extract browser information from the user agent.
     *
     * @param string|null $userAgent
     * @return string|null
     */
    private function getBrowserFromUserAgent(?string $userAgent): ?string
    {
        if (!$userAgent) return null;

        if (str_contains($userAgent, 'Chrome')) return 'Chrome';
        if (str_contains($userAgent, 'Firefox')) return 'Firefox';
        if (str_contains($userAgent, 'Safari')) return 'Safari';
        if (str_contains($userAgent, 'Edge')) return 'Edge';

        return 'Unknown';
    }

    /**
     * Get location from the IP address (simplified version).
     *
     * @param string|null $ip
     * @return string|null
     */
    private function getLocationFromIP(?string $ip): ?string
    {
        // This is a simplified version.
        return $ip ? 'Your Location' : null;
    }

}
