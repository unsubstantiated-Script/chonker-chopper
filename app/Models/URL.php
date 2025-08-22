<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class URL extends Model
{
    use HasFactory;

    /**
     * The url table associated with the model.
     * @var string $table
     */
    protected $table = 'urls';

    /**
     * The attributes that are mass assignable.
     * @var string[] $fillable
     */
    protected $fillable = [
        'batch_id',
        'original_url',
        'short_url',
    ];

    /**
     * The analytics relationship.
     * @var mixed $analytics
     */
    private mixed $analytics;

    /**
     * Define a one-to-many relationship with the URLAnalytics model.
     *
     * @return HasMany
     */
    public function analytics()
    {
        return $this->hasMany(URLAnalytics::class, 'url_id');
    }

    /**
     * Retrieve the total clicks count by summing up the 'clicks' column in the analytics data.
     * @return int
     */
    public function getTotalClicksAttribute(): int
    {
        return $this->analytics->sum('clicks');
    }

    /**
     * Scope a query to filter results by a specific batch ID.
     *
     * @param Builder $query
     * @param int $batchId
     * @return Builder
     */
    public function scopeByBatchId(Builder $query, int $batchId): Builder
    {
        return $query->where('batch_id', $batchId);
    }
}
