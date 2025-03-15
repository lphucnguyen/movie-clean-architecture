<?php

namespace App\Shared\Domain\Concerns;

trait HasRates
{
    public function rate($user, $rating)
    {
        $this->ratings()->updateOrCreate(
            [
                'user_id' => $user->id,
                'film_id' => $this->id
            ],
            ['rating' => $rating]
        );

        $responce = array(
            "status" => true,
            "avg" => $this->ratings->avg('rating') ?? 0,
            "count" => $this->ratings->count() . ' Reviews'
        );

        // $connection = new Connection();
        // $client = $connection->getClient();

        // $query = 'MATCH (u:Users{id: ' . $user->id . '})-[r:RATED]->(f:Films{id: ' . $this->id . '})
        //                 DELETE r';

        // $param = [
        //     'filmId' => $this->id,
        //     'userId' => $user->id
        // ];
        // $client->run($query, $param);

        // $query = 'MERGE (u1:Users{id: $userId})
        //             MERGE (f1:Films{id: $filmId})
        //             MERGE (u1)-[r1:RATED {user_id: ' . $user->id .
        //                 ', film_id: ' . $this->id .
        //                 ', rating: ' . $rating . '}]->(f1)
        //             RETURN type(r1)';

        // $param = [
        //     'filmId' => $this->id,
        //     'userId' => $user->id
        // ];
        // $client->run($query, $param);

        return $responce;
    }
}
