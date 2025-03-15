<?php

namespace App\Shared\Domain\Concerns;

trait HasReviews
{
    public function review($user, $title, $review)
    {
        $this->reviews()->updateOrCreate(
            [
                'user_id' => $user->id,
                'film_id' => $this->id
            ],
            ['title' => $title, 'review' => $review]
        );

        return true;
    }

    public function deleteReview($user)
    {
        $this->reviews()->where('user_id', $user->id)->delete();
    }
}
