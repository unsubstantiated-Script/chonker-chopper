<?php

namespace Tests\Unit\Models;

use App\Models\URL;
use App\Models\URLAnalytics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class URLTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_has_correct_fillable_attributes(): void
    {
        $url = new URL();

        $this->assertEquals([
            'batch_id',
            'original_url',
            'short_url',
        ], $url->getFillable());
    }

    public function test_it_uses_correct_table_name(): void
    {
        $url = new URL();

        $this->assertEquals('urls', $url->getTable());
    }

    public function test_it_has_analytics_relationship(): void
    {
        $url = URL::factory()->create();
        URLAnalytics::factory()->count(3)->create(['url_id' => $url->id]);

        $this->assertCount(3, $url->analytics);
        $this->assertInstanceOf(URLAnalytics::class, $url->analytics->first());
    }

    public function test_it_calculates_total_clicks_correctly(): void
    {
        $url = URL::factory()->create();
        URLAnalytics::factory()->count(5)->create(['url_id' => $url->id]);

        // Since each analytics record represents one click
        $this->assertEquals(5, $url->total_clicks);
    }

    public function test_it_returns_zero_clicks_when_no_analytics(): void
    {
        $url = URL::factory()->create();

        $this->assertEquals(0, $url->total_clicks);
    }

    public function test_it_can_filter_by_batch_id(): void
    {
        URL::factory()->create(['batch_id' => 'batch-1']);
        URL::factory()->create(['batch_id' => 'batch-2']);
        URL::factory()->count(2)->create(['batch_id' => 'batch-1']);

        $results = URL::byBatchId('batch-1')->get();

        $this->assertCount(3, $results);
        $results->each(function ($url) {
            $this->assertEquals('batch-1', $url->batch_id);
        });
    }

    public function test_scope_by_batch_id_returns_empty_collection_for_nonexistent_batch(): void
    {
        URL::factory()->create(['batch_id' => 'batch-1']);

        $results = URL::byBatchId('nonexistent-batch')->get();

        $this->assertCount(0, $results);
    }
}
