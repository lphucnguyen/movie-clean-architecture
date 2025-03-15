<?php

namespace App\Shared\Domain\Concerns;

trait HasFavorites
{
    public function isInFavorite($user)
    {
        if ($user != null) {
            $favorites = $user->favorites;

            foreach ($favorites as $favorite) {
                if ($favorite->user_id == $user->id && $favorite->film_id == $this->id) {
                    return true;
                }
            }

            return false;
        }
    }

    public function addToFavorite($user)
    {
        if (!$this->isInFavorite($user)) {
            $this->favorites()->create([
                'user_id' => $user->id,
                'film_id' => $this->id,
            ]);
            return true;
        }

        return false;
    }

    public function removeFromFavorite($user)
    {
        if ($this->isInFavorite($user)) {
            $this->favorites()->where('user_id', $user->id)->delete();
            return true;
        }

        return false;
    }
}
