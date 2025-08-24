<?php

namespace Tests\Feature\Controllers;

use App\Models\URL;
use App\Models\URLAnalytics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class URLControllerTest extends TestCase
{
    use RefreshDatabase;

    private const TEST_CSV_PATH = 'urls_2_test.csv';

    // ==================== WEB ROUTE TESTS ====================

    public function test_create_displays_upload_form(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('urls.create');
    }


    public function test_store_processes_csv_file_correctly(): void
    {
        Storage::fake('local');

        $testCsvPath = base_path(self::TEST_CSV_PATH);
        $this->assertFileExists($testCsvPath, 'Test CSV file does not exist');

        $csvContent = file_get_contents($testCsvPath);
        $uploadedFile = UploadedFile::fake()->createWithContent(self::TEST_CSV_PATH, $csvContent);

        $response = $this->post('/', [
            'csv_file' => $uploadedFile
        ]);

        $response->assertRedirect('/view-urls');
        $response->assertSessionHas('batch_id');
        $this->assertDatabaseCount('urls', $this->getExpectedUrlCount());
    }

    public function test_index_displays_urls_for_current_batch(): void
    {
        Session::put('batch_id', 'current-batch');
        $this->createUrlsFromTestCsv('current-batch');

        // Create URLs in a different batch to ensure filtering works
        URL::factory()->create(['batch_id' => 'different-batch']);

        $response = $this->get('/view-urls');

        $response->assertStatus(200);
        $response->assertViewIs('urls.index');
        $response->assertViewHas('urlBatches');

        $urlBatches = $response->viewData('urlBatches');

        // Find the current batch in the response
        $currentBatch = $urlBatches->firstWhere('batch_id', 'current-batch');
        $this->assertNotNull($currentBatch, 'Current batch not found in response');

        // Check that the current batch has the expected number of URLs
        $this->assertEquals($this->getExpectedUrlCount(), $currentBatch['total_urls']);
        $this->assertCount($this->getExpectedUrlCount(), $currentBatch['urls']);

        // Verify that the different batch also exists
        $differentBatch = $urlBatches->firstWhere('batch_id', 'different-batch');
        $this->assertNotNull($differentBatch, 'Different batch not found in response');
        $this->assertEquals(1, $differentBatch['total_urls']);
    }

    public function test_analytics_displays_urls_with_analytics_data(): void
    {
        Session::put('batch_id', 'test-batch');
        $urls = $this->createUrlsFromTestCsv('test-batch');

        URLAnalytics::factory()->count(3)->create(['url_id' => $urls->first()->id]);

        $response = $this->get('/analytics');

        $response->assertStatus(200);
        $response->assertViewIs('urls.analytics');
        $response->assertViewHas('analyticsBatches');
    }

    public function test_redirect_redirects_to_original_url(): void
    {
        $urls = $this->createUrlsFromTestCsv();
        $testUrl = $urls->first();

        $response = $this->get('/' . $testUrl->short_url);

        $response->assertRedirect($testUrl->original_url);
    }

    public function test_redirect_creates_analytics_record(): void
    {
        $urls = $this->createUrlsFromTestCsv();
        $testUrl = $urls->first();

        $this->get('/' . $testUrl->short_url);

        $this->assertDatabaseHas('url_analytics', [
            'url_id' => $testUrl->id
        ]);
    }

    public function test_redirect_returns_404_for_nonexistent_short_url(): void
    {
        $response = $this->get('/nonexistent');

        $response->assertStatus(404);
    }

    // ==================== API ROUTE TESTS ====================

    public function test_api_returns_urls_as_json(): void
    {
        // Don't set session for API tests - create URLs with a known batch ID
        $urls = $this->createUrlsFromTestCsv('api-test-batch');

        $response = $this->getJson('/api/v1/urls');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'batch_id',
                        'created_at',
                        'urls',
                        'total_urls'
                    ]
                ]
            ]);

        // Verify the API response contains our test batch
        $responseData = $response->json('data');
        $testBatch = collect($responseData)->firstWhere('batch_id', 'api-test-batch');

        $this->assertNotNull($testBatch, 'Test batch not found in API response');
        $this->assertEquals($this->getExpectedUrlCount(), $testBatch['total_urls']);
    }

    public function test_api_can_upload_csv_file(): void
    {
        Storage::fake('local');

        $testCsvPath = base_path(self::TEST_CSV_PATH);
        $this->assertFileExists($testCsvPath, 'Test CSV file does not exist');

        $csvContent = file_get_contents($testCsvPath);
        $uploadedFile = UploadedFile::fake()->createWithContent(self::TEST_CSV_PATH, $csvContent);

        $response = $this->postJson('/api/v1/urls/upload', [
            'csv_file' => $uploadedFile
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'urls_created',
                'batch_id'
            ]);

        $this->assertDatabaseCount('urls', $this->getExpectedUrlCount());
    }

    public function test_api_can_get_url_by_short_code(): void
    {
        $urls = $this->createUrlsFromTestCsv();

        // Debug: Check if URLs were actually created
        $this->assertGreaterThan(0, $urls->count(), 'No URLs were created from CSV');
        $this->assertDatabaseCount('urls', $urls->count());

        $testUrl = $urls->first();

        // Debug: Verify the URL exists in database
        $this->assertDatabaseHas('urls', [
            'short_url' => $testUrl->short_url
        ]);

        $response = $this->getJson('/api/v1/urls/' . $testUrl->short_url);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'original_url',
                    'short_url',
                    'batch_id'
                ],
                'status'
            ])
            ->assertJson([
                'data' => [
                    'short_url' => $testUrl->short_url,
                    'original_url' => $testUrl->original_url
                ]
            ]);
    }

    public function test_api_returns_404_for_nonexistent_url(): void
    {
        $response = $this->getJson('/api/v1/urls/nonexistent');

        $response->assertStatus(404)
            ->assertJsonStructure([
                'message'
            ]);
    }

    // ==================== HELPER METHODS ====================

    private function createUrlsFromTestCsv(?string $batchId = null): \Illuminate\Database\Eloquent\Collection
    {
        $urls = new \Illuminate\Database\Eloquent\Collection();
        $batch = $batchId ?? 'test-batch-' . Str::random(8);

        $csvPath = base_path(self::TEST_CSV_PATH);
        if (file_exists($csvPath)) {
            $csvData = array_map('str_getcsv', file($csvPath));

            // Don't skip any rows since there's no header
            foreach ($csvData as $row) {
                if (!is_array($row) || empty($row[0])) {
                    continue;
                }

                $url = trim($row[0]);

                if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                    $urlModel = URL::create([
                        'batch_id' => $batch,
                        'original_url' => $url,
                        'short_url' => Str::random(6),
                    ]);

                    $urls->push($urlModel);
                }
            }
        }

        return $urls;
    }

    private function getExpectedUrlCount(): int
    {
        $csvPath = base_path(self::TEST_CSV_PATH);
        if (!file_exists($csvPath)) {
            return 0;
        }

        $csvData = array_map('str_getcsv', file($csvPath));
        // Don't remove first row since there's no header

        return count(array_filter($csvData, function ($row) {
            return !empty($row[0]) && filter_var(trim($row[0]), FILTER_VALIDATE_URL);
        }));
    }


}
