<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class URLAnalytics extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * @var string $table
     */
    protected $table = 'url_analytics';


    /**
     * The attributes that are mass assignable.
     * @var string[] $fillable
     */
    protected $fillable = [
        'url_id',
        'geographic_location',
        'browser',
        'user_agent',
        'ip_address',
        'referrer',
        'clicked_at',
    ];

    /**
     * The attributes that should be cast.
     * @var array<string, string>
     */
    protected $casts = [
        'clicked_at' => 'datetime',
    ];


    /**
     * Define the inverse relationship with URL model.
     * @return BelongsTo
     */
    public function url(): BelongsTo
    {
        return $this->belongsTo(URL::class);
    }


    /**
     * Get formatted clicked_at time.
     * @return string
     */
    public function getFormattedClickedAtAttribute(): string
    {
        return $this->clicked_at->format('M j, Y g:i A');
    }

    /**
     * Extract browser name from user agent.
     * @return string
     */
    public function getBrowserNameAttribute(): string
    {
        if (empty($this->user_agent)) {
            return 'Unknown';
        }

        $browsers = [
            'Chrome' => '/Chrome/i',
            'Firefox' => '/Firefox/i',
            'Safari' => '/Safari/i',
            'Edge' => '/Edge/i',
            'Opera' => '/Opera/i',
            'Internet Explorer' => '/Trident/i',
        ];

        foreach ($browsers as $browser => $pattern) {
            if (preg_match($pattern, $this->user_agent)) {
                return $browser;
            }
        }

        return 'Other';
    }
}
