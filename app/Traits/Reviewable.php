<?php namespace App\Traits;

use App\Models\Review;

trait Reviewable
{
    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function clone_reviews_from($old_owner)
    {
        foreach($old_owner->reviews->where('result_status', 0) as $review) {
            $this->make_review($review->review, ($this->commercial_offer_id ?? $this->com_offer_id));
        }

        return $this->reviews;
    }

    public function make_review($text, $com_offer_id = null)
    {
        if($this->id) {
            $this->reviews()->create(['reviewable_id' => $this->id,'review' => $text, 'commercial_offer_id' => $com_offer_id]);
            $this->push();

            return true;
        }
        return false;
    }
}