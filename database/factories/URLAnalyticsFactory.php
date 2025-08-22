<?php

namespace Database\Factories;

use App\Models\URL;
use App\Models\URLAnalytics;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\URLAnalytics>
 */
class URLAnalyticsFactory extends Factory
{
    protected $model = URLAnalytics::class;

    public function definition(): array
    {
        return [
            'url_id' => URL::factory(),
            'geographic_location' => fake()->country(),
            'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
            'user_agent' => fake()->userAgent(),
            'ip_address' => fake()->ipv4(),
            'referrer' => fake()->url(),
            'clicked_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
