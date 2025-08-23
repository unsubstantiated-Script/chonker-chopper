<?php

namespace Tests\Unit\Models;

use App\Models\URL;
use App\Models\URLAnalytics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class URLTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests that the URL model has the correct fillable attributes.
     * @return void
     */
    public function test_it_has_correct_fillable_attributes(): void
    {
        $url = new URL();

        $this->assertEquals([
            'batch_id',
            'original_url',
            'short_url',
        ], $url->getFillable());
    }

    /**
     * Tests that the URL model uses the correct table name.
     * @return void
     */
    public function test_it_uses_correct_table_name(): void
    {
        $url = new URL();

        $this->assertEquals('urls', $url->getTable());
    }

    /**
     * Tests the analytics relationship of the URL model.
     *
     * Ensures that a URL model can have multiple related analytics records.
     */
    public function test_it_has_analytics_relationship(): void
    {
        $url = URL::factory()->create();
        URLAnalytics::factory()->count(3)->create(['url_id' => $url->id]);

        $this->assertCount(3, $url->analytics);
        $this->assertInstanceOf(URLAnalytics::class, $url->analytics->first());
    }

    /**
     * Tests that filtering by batch ID returns the correct number of results
     * and ensures each result matches the specified batch ID.
     * @return void
     */
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

    /**
     * Tests the scope `byBatchId` to ensure it returns an empty collection
     * when querying with a nonexistent batch ID.
     * @return void
     */
    public function test_scope_by_batch_id_returns_empty_collection_for_nonexistent_batch(): void
    {
        URL::factory()->create(['batch_id' => 'batch-1']);

        $results = URL::byBatchId('nonexistent-batch')->get();

        $this->assertCount(0, $results);
    }
}
