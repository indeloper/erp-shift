<?php

namespace App\Console\Commands;

use App\Models\CommercialOffer\CommercialOfferMaterialSplit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefactorSplitsDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'splits:refactor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'refactor old splits structure';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        DB::beginTransaction();

        CommercialOfferMaterialSplit::where('count', 0)->delete();
        $splits = CommercialOfferMaterialSplit::all();

        $start_count = $splits->count();
        $com_offer_splits = $splits->groupBy('com_offer_id');

        $com_offer_splits = $com_offer_splits->map(function ($group) {
            $material_splits = $group->groupBy('man_mat_id');

            return $material_splits->map(function ($manual_group) {
                $type_groups = $manual_group->groupBy('type');
                $type_groups = $type_groups->map(function ($type) {
                    return $type->reduce(function ($carry, $duplicate) {
                        if (! $carry) {
                            return $duplicate;
                        } else {
                            $carry->count += $duplicate->count;
                            $duplicate->delete();

                            return $carry;
                        }
                    });
                });
                $manual_group = $type_groups->flatten()->sortBy('type');
                $parent_split = $manual_group->whereIn('type', [1, 3, 5])->first();
                foreach ($manual_group as $mat) {
                    if ($mat->id != $parent_split->id) {
                        if (in_array($mat->type, [3, 5])) {
                            $mat->parent_id = $parent_split->id;
                        } elseif ($mat->type == 2) {
                            $mat->parent_id = $manual_group->where('type', 1)->first()->id ?? $parent_split->id;
                        } elseif ($mat->type == 4) {
                            $mat->parent_id = $manual_group->where('type', 3)->first()->id ?? $parent_split->id;
                        } else {
                            dump($mat, $parent_split->id);
                            dd('something went wrong, operation aborted');
                        }
                    }
                    $mat->save();
                }

                return $manual_group;
            });
        });

        $diff = $start_count - CommercialOfferMaterialSplit::count();
        $answer = $this->ask("Будет удалено {$diff} сплитов. Это пустые и дубликаты, которые были объединены.\n".
        " Если команда запущена после обновления логики сплитов, то могут быть удалены не дубликаты, а просто разделённые материалы.\n Вы уверены? (1/0)");
        if ($answer != 1) {
            DB::rollBack();
            $this->info('Операция отменена. Успехов!');
        } else {
            DB::commit();
            $this->info('Операция закончена. Успехов!');
        }
    }
}
