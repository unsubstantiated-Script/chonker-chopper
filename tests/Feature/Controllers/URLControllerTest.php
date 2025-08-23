<?php


namespace Tests\Feature\Controllers;

use App\Models\URL;
use App\Models\URLAnalytics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class URLControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test to verify that the /create route displays the upload form correctly.
     * @return void
     */
    public function test_create_displays_upload_form(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('urls.create');
    }

    /**
     * Test to ensure the /view-urls route displays only the URLs
     * associated with the current batch session.
     * @return void
     */
    public function test_index_displays_urls_for_current_batch(): void
    {
        // Create URLs for the current session
        Session::put('batch_id', 'current-batch');
        URL::factory()->count(2)->create(['batch_id' => 'current-batch']);

        // Create URLs for different batch
        URL::factory()->create(['batch_id' => 'different-batch']);

        $response = $this->get('/view-urls');

        $response->assertStatus(200);
        $response->assertViewIs('urls.index');
        $response->assertViewHas('urlBatches');;

        $urls = $response->viewData('urlBatches');
        $this->assertCount(2, $urls);
    }

    /**
     * Tests that the analytics page displays URLs with their associated analytics data.
     *
     * - Sets a batch ID in the session to filter data.
     * - Creates a URL associated with the batch ID.
     * - Generates multiple analytics data records for the created URL.
     * - Sends a request to the analytics endpoint.
     * - Asserts the HTTP response status and view returned.
     * - Verifies the presence of analytics batches in the view data.
     * - Checks that the correct number of URLs is retrieved and that their analytics relation is loaded.
     * @return void
     */
    public function test_analytics_displays_urls_with_analytics_data(): void
    {
        Session::put('batch_id', 'test-batch');
        $url = URL::factory()->create(['batch_id' => 'test-batch']);
        URLAnalytics::factory()->count(3)->create(['url_id' => $url->id]);

        $response = $this->get('/analytics');

        $response->assertStatus(200);
        $response->assertViewIs('urls.analytics');
        $response->assertViewHas('analyticsBatches');;

        $urls = $response->viewData('urls');
        $this->assertCount(1, $urls);
        $this->assertTrue($urls->first()->relationLoaded('analytics'));
    }

    /**
     * Tests that the redirect functionality redirects a short URL to its original URL.
     *
     * - Creates a URL record with a specific short URL and an associated original URL.
     * - Sends a request to the endpoint corresponding to the short URL.
     * - Asserts that the response redirects to the original URL.
     * @return void
     */
    public function test_redirect_redirects_to_original_url(): void
    {
        $url = URL::factory()->create([
            'short_url' => 'abc123',
            'original_url' => 'https://example.com'
        ]);

        $response = $this->get('/abc123');

        $response->assertRedirect('https://example.com');
    }

    /**
     * Tests that accessing a short URL creates a corresponding analytics record.
     *
     * - Creates a URL with a specified short URL value.
     * - Sends a GET request to the short URL endpoint.
     * - Asserts that the database contains an analytics record linked to the URL.
     * @return void
     */
    public function test_redirect_creates_analytics_record(): void
    {
        $url = URL::factory()->create(['short_url' => 'abc123']);

        $this->get('/abc123');

        $this->assertDatabaseHas('url_analytics', [
            'url_id' => $url->id
        ]);
    }

    /**
     * Tests that the redirect endpoint accurately tracks the user's browser, user agent, and IP address.
     *
     * - Creates a URL with a predefined short URL for redirection.
     * - Sends a request to the short URL, including specific headers for User-Agent and forwarded IP.
     * - Asserts that the analytics database table contains a record with the correct URL ID, browser type, and user agent.
     * @return void
     */
    public function test_redirect_tracks_user_agent_and_ip(): void
    {
        $url = URL::factory()->create(['short_url' => 'abc123']);

        $response = $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Chrome/91.0)',
            'HTTP_X_FORWARDED_FOR' => '192.168.1.1'
        ])->get('/abc123');

        $this->assertDatabaseHas('url_analytics', [
            'url_id' => $url->id,
            'browser' => 'Chrome',
            'user_agent' => 'Mozilla/5.0 (Chrome/91.0)',
        ]);
    }

    /**
     * Tests that the redirection process tracks the referrer information.
     *
     * - Creates a URL with a specific short URL identifier.
     * - Sends a request to the URL while including a referer header.
     * - Verifies that the referrer information is correctly recorded in the database
     *   under the URL analytics table, associated with the appropriate URL ID.
     * @return void
     */
    public function test_redirect_tracks_referrer(): void
    {
        $url = URL::factory()->create(['short_url' => 'abc123']);

        $this->withHeaders([
            'referer' => 'https://google.com'
        ])->get('/abc123');

        $this->assertDatabaseHas('url_analytics', [
            'url_id' => $url->id,
            'referrer' => 'https://google.com'
        ]);
    }

    /**
     * Tests that the redirect endpoint returns a 404 status for a nonexistent short URL.
     *
     * - Sends a GET request to a nonexistent short URL endpoint.
     * - Asserts that the HTTP response status is 404.
     * @return void
     */
    public function test_redirect_returns_404_for_nonexistent_short_url(): void
    {
        $response = $this->get('/nonexistent');

        $response->assertStatus(404);
    }

    /**
     * Tests that browser detection works correctly when accessing a short URL.
     *
     * - Creates a URL with a specific short URL identifier.
     * - Sends a request to the short URL endpoint with a User-Agent header indicating Chrome.
     * - Verifies that the analytics database records the correct browser as Chrome.
     * - Clears the analytics database records to isolate the next test scenario.
     * - Sends another request to the short URL endpoint with a User-Agent header indicating Firefox.
     * - Verifies that the analytics database records the correct browser as Firefox.
     * @return void
     */
    public function test_browser_detection_works_correctly(): void
    {
        $url = URL::factory()->create(['short_url' => 'abc123']);

        // Test Chrome detection
        $this->withHeaders(['User-Agent' => 'Chrome/91.0'])
            ->get('/abc123');

        $this->assertDatabaseHas('url_analytics', ['browser' => 'Chrome']);

        // Clean up for next test
        URLAnalytics::truncate();

        // Test Firefox detection
        $this->withHeaders(['User-Agent' => 'Firefox/89.0'])
            ->get('/abc123');

        $this->assertDatabaseHas('url_analytics', ['browser' => 'Firefox']);
    }

}
