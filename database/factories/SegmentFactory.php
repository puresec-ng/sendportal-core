<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Segment;

class SegmentFactory extends Factory
{
    /** @var string */
    protected $model = Segment::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'name' => ucwords($this->faker->unique()->word)
        ];
    }
}
