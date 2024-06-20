<?php

declare(strict_types=1);

namespace App\Services\Project;

use App\Models\Project;

final class ProjectService
{

    /**
     * @param  string  $title
     * @param  bool  $status
     *
     * @return \App\Models\Project
     */
    public function store(string $name, bool $status = false): Project
    {
        return Project::query()->create([
            'name'   => $name,
            'status' => $status,
        ]);
    }

}