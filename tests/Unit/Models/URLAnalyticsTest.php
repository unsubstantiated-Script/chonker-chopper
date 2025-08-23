<?php

namespace Tests\Unit\Models;

use App\Models\URL;
use App\Models\URLAnalytics;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class URLAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the URLAnalytics model has the correct fillable attributes.
     * @return void
     */
    public function test_it_has_correct_fillable_attributes(): void
    {
        $analytics = new URLAnalytics();

        $this->assertEquals([
            'url_id',
            'geographic_location',
            'browser',
            'user_agent',
            'ip_address',
            'referrer',
            'clicked_at',
        ], $analytics->getFillable());
    }

    /**
     * Test that the URLAnalytics model correctly casts the 'clicked_at' attribute to a Carbon instance.
     * @return void
     */
    public function test_it_casts_clicked_at_to_datetime(): void
    {
        $analytics = URLAnalytics::factory()->create([
            'clicked_at' => '2025-01-15 15:30:00'
        ]);

        $this->assertInstanceOf(Carbon::class, $analytics->clicked_at);
    }

    /**
     * Test the relationship between URLAnalytics and URL models.
     * @return void
     */
    public function test_it_belongs_to_url(): void
    {
        $url = URL::factory()->create();
        $analytics = URLAnalytics::factory()->create(['url_id' => $url->id]);

        $this->assertInstanceOf(URL::class, $analytics->url);
        $this->assertEquals($url->id, $analytics->url->id);
    }

    /**
        * Test the formatted_clicked_at accessor.
        * @return void
        */
    public function test_it_formats_clicked_at_date_correctly(): void
    {
        $analytics = URLAnalytics::factory()->make([
            'clicked_at' => Carbon::create(2025, 1, 15, 15, 30, 0)
        ]);

        $this->assertEquals('Jan 15, 2025 3:30 PM', $analytics->formatted_clicked_at);
    }

    /**
     * Test the browser_name accessor to ensure it extracts the Chrome browser name from the user_agent string.
     * @return void
     */
    public function test_it_extracts_chrome_browser_name(): void
    {
        $analytics = URLAnalytics::factory()->make([
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        ]);

        $this->assertEquals('Chrome', $analytics->browser_name);
    }

    /**
     * Test the extraction of the browser name as "Firefox" from the user agent string.
     * @return void
     */
    public function test_it_extracts_firefox_browser_name(): void
    {
        $analytics = URLAnalytics::factory()->make([
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0'
        ]);

        $this->assertEquals('Firefox', $analytics->browser_name);
    }

    /**
     * Test the extraction of the browser name as "Safari" from the user agent string.
     * @return void
     */
    public function test_it_extracts_safari_browser_name(): void
    {
        $analytics = URLAnalytics::factory()->make([
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15'
        ]);

        $this->assertEquals('Safari', $analytics->browser_name);
    }

    /**
     * Test the browser_name accessor returns 'Unknown' for an empty user_agent.
     * @return void
     */
    public function test_it_returns_unknown_for_empty_user_agent(): void
    {
        $analytics = URLAnalytics::factory()->make(['user_agent' => null]);

        $this->assertEquals('Unknown', $analytics->browser_name);
    }


    /**
     * Test the browser_name accessor for unrecognized user agents.
     * @return void
     */
    public function test_it_returns_other_for_unrecognized_browser(): void
    {
        $analytics = URLAnalytics::factory()->make([
            'user_agent' => 'SomeWeirdBrowser/1.0'
        ]);

        $this->assertEquals('Other', $analytics->browser_name);
    }
}
