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

}
