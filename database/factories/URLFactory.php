<?php

namespace Database\Factories;

use App\Models\URL;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\URL>
 */
class URLFactory extends Factory
{
    protected $model = URL::class;

    public function definition(): array
    {
        return [
            'batch_id' => fake()->uuid(),
            'original_url' => fake()->url(),
            'short_url' => fake()->slug(2),
        ];
    }
}
