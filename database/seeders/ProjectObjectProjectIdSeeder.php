<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectObjectProjectIdSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Project::query()
            ->with('object')
            ->lazy()
            ->last(function (Project $project) {
                if ($project->object_id === null) {
                    return;
                }

                $project->object->update([
                    'project_id' => $project->id,
                ]);
            });
    }

}
