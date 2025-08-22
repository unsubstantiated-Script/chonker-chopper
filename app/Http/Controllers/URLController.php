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
        $request->validate([
            'original_url' => 'required|url|max:2048',
        ]);

        $url = URL::create([
            'batch_id' => $this->getBatchId(),
            'original_url' => $request->original_url,
            'short_url' => $this->generateShortUrl(),
        ]);

        return redirect()->route('urls.view')->with('success', 'URL shortened successfully!');
    }

    /**
     * Display all URLs for the current batch.
     *
     * @return View
     */
    public function index(): View
    {
        $urls = URL::byBatchId($this->getBatchId())->get();

        return view('urls.index', compact('urls'));
    }

    /**
     * Display analytics for all URLs.
     *
     * @return View
     */
    public function analytics(): View
    {
        $urls = URL::with('analytics')->byBatchId($this->getBatchId())->get();

        return view('urls.analytics', compact('urls'));
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
        return $ip ? 'Unknown Location' : null;
    }

}
