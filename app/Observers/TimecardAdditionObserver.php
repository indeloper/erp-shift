<?php

namespace App\Observers;

use App\Models\HumanResources\Timecard;
use App\Models\HumanResources\TimecardAddition;

class TimecardAdditionObserver
{
    /**
     * Handle the timecard "updated" event.
     *
     * @param  TimecardAddition  $addition
     * @return void
     */
    public function updated(TimecardAddition $addition): void
    {
        $addition->generateAction('update');
        $new_values = $addition->getDirty();
        if ($addition->type == 1 and isset($new_values['prolonged'])) {
            if ($new_values['prolonged']) {

                $card = $addition->timecard()->first();

                $next_card_month = $card->month == 12 ? 1 : $card->month + 1;
                $next_card_year = $card->month == 12 ? $card->year + 1 : $card->year;
                $card_next = Timecard::where('month', $next_card_month)
                    ->where('year', $next_card_year)
                    ->where('user_id', $card->user_id)
                    ->first();

                if ($card_next) {
                    $addition_exists = $card_next->additions()
                        ->where('type', 1)
                        ->where('name', $addition->name)
                        ->where('amount', $addition->amount)->exists();
                    if (!$addition_exists) {
                        $card_next->additions()->save($addition->replicate());
                    }
                }
            } else {
                $card = $addition->timecard()->first();
                $next_card_month = $card->month == 12 ? 1 : $card->month + 1;
                $next_card_year = $card->month == 12 ? $card->year + 1 : $card->year;
                $card_next = Timecard::where('month', $next_card_month)
                    ->where('year', $next_card_year)
                    ->where('user_id', $card->user_id)
                    ->first();

                if ($card_next) {
                    $addition_exists = $card_next->additions()
                        ->where('type', 1)
                        ->where('name', $addition->name)
                        ->where('amount', $addition->amount)->first();
                    if ($addition_exists) {
                        $addition_exists->delete();
                    }
                }
            }
        }
    }

    /**
     * Handle the timecard "created" event.
     *
     * @param  TimecardAddition  $addition
     * @return void
     */
    public function created(TimecardAddition $addition): void
    {
        $addition->generateAction();
        if ($addition->type == 1 and $addition->prolonged == 1) {
            $card = $addition->timecard()->first();
            $next_card_month = $card->month == 12 ? 1 : $card->month + 1;
            $next_card_year = $card->month == 12 ? $card->year + 1 : $card->year;
            $card_next = Timecard::where('month', $next_card_month)
                ->where('year', $next_card_year)
                ->where('user_id', $card->user_id)
                ->first();
            if ($card_next) {
                $addition_exists = $card_next->additions()
                    ->where('type', 1)
                    ->where('name', $addition->name)
                    ->where('amount', $addition->amount)->exists();
                if (!$addition_exists) {
                    $new_addition = $addition->replicate();
                    $new_addition->timecard_id = $card_next->id;
                    $new_addition->save();
                }
            }
        }
    }

    /**
     * Handle the timecard "deleting" event.
     *
     * @param  TimecardAddition  $addition
     * @return void
     */
    public function deleted(TimecardAddition $addition): void
    {
        $addition->generateAction('delete');
        if ($addition->type == 1 and $addition->prolonged == 1) {
            $card = $addition->timecard()->first();
            $next_card_month = $card->month == 12 ? 1 : $card->month + 1;
            $next_card_year = $card->month == 12 ? $card->year + 1 : $card->year;
            $card_next = Timecard::where('month', $next_card_month)
                ->where('year', $next_card_year)
                ->where('user_id', $card->user_id)
                ->first();

            if ($card_next) {
                $addition_exists = $card_next->additions()
                    ->where('type', 1)
                    ->where('name', $addition->name)
                    ->where('amount', $addition->amount)->first();
                if ($addition_exists) {
                    $addition_exists->delete();
                }
            }
        }
    }
}
