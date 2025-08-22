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

    public function test_create_displays_upload_form(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('urls.create');
    }

    public function test_store_creates_url_with_valid_data(): void
    {
        $response = $this->post('/', [
            'original_url' => 'https://example.com'
        ]);

        $this->assertDatabaseHas('urls', [
            'original_url' => 'https://example.com'
        ]);

        $response->assertRedirect(route('urls.view'));
        $response->assertSessionHas('success', 'URL shortened successfully!');
    }

    public function test_store_validates_required_original_url(): void
    {
        $response = $this->post('/', []);

        $response->assertSessionHasErrors('original_url');
        $this->assertDatabaseCount('urls', 0);
    }

    public function test_store_validates_url_format(): void
    {
        $response = $this->post('/', [
            'original_url' => 'not-a-valid-url'
        ]);

        $response->assertSessionHasErrors('original_url');
        $this->assertDatabaseCount('urls', 0);
    }

    public function test_store_validates_url_max_length(): void
    {
        $longUrl = 'https://example.com/' . str_repeat('a', 2048);

        $response = $this->post('/', [
            'original_url' => $longUrl
        ]);

        $response->assertSessionHasErrors('original_url');
        $this->assertDatabaseCount('urls', 0);
    }

    public function test_store_generates_unique_short_url(): void
    {
        $this->post('/', ['original_url' => 'https://example1.com']);
        $this->post('/', ['original_url' => 'https://example2.com']);

        $urls = URL::all();
        $this->assertCount(2, $urls);
        $this->assertNotEquals($urls[0]->short_url, $urls[1]->short_url);
    }

    public function test_store_uses_same_batch_id_for_session(): void
    {
        $this->post('/', ['original_url' => 'https://example1.com']);
        $this->post('/', ['original_url' => 'https://example2.com']);

        $urls = URL::all();
        $this->assertCount(2, $urls);
        $this->assertEquals($urls[0]->batch_id, $urls[1]->batch_id);
    }

    public function test_index_displays_urls_for_current_batch(): void
    {
        // Create URLs for current session
        Session::put('batch_id', 'current-batch');
        URL::factory()->count(2)->create(['batch_id' => 'current-batch']);

        // Create URLs for different batch
        URL::factory()->create(['batch_id' => 'different-batch']);

        $response = $this->get('/view-urls');

        $response->assertStatus(200);
        $response->assertViewIs('urls.index');
        $response->assertViewHas('urls');

        $urls = $response->viewData('urls');
        $this->assertCount(2, $urls);
    }

    public function test_analytics_displays_urls_with_analytics_data(): void
    {
        Session::put('batch_id', 'test-batch');
        $url = URL::factory()->create(['batch_id' => 'test-batch']);
        URLAnalytics::factory()->count(3)->create(['url_id' => $url->id]);

        $response = $this->get('/analytics');

        $response->assertStatus(200);
        $response->assertViewIs('urls.analytics');
        $response->assertViewHas('urls');

        $urls = $response->viewData('urls');
        $this->assertCount(1, $urls);
        $this->assertTrue($urls->first()->relationLoaded('analytics'));
    }

    public function test_redirect_redirects_to_original_url(): void
    {
        $url = URL::factory()->create([
            'short_url' => 'abc123',
            'original_url' => 'https://example.com'
        ]);

        $response = $this->get('/abc123');

        $response->assertRedirect('https://example.com');
    }

    public function test_redirect_creates_analytics_record(): void
    {
        $url = URL::factory()->create(['short_url' => 'abc123']);

        $this->get('/abc123');

        $this->assertDatabaseHas('url_analytics', [
            'url_id' => $url->id
        ]);
    }

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

    public function test_redirect_returns_404_for_nonexistent_short_url(): void
    {
        $response = $this->get('/nonexistent');

        $response->assertStatus(404);
    }

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

    public function test_different_sessions_get_different_batch_ids(): void
    {
        // First session
        $this->post('/', ['original_url' => 'https://example1.com']);
        $firstBatchId = session('batch_id');

        // Start new session
        $this->session([]);

        // Second session
        $this->post('/', ['original_url' => 'https://example2.com']);
        $secondBatchId = session('batch_id');

        $this->assertNotEquals($firstBatchId, $secondBatchId);
    }
}
