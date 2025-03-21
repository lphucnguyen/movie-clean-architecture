<?php

namespace App\Application\DTOs\Review;

use App\Shared\Application\DTOs\BaseDTO;

class GetReviewsDTO extends BaseDTO
{
    public string|null $searchKey = null;
    public array|string|null $searchRating = null;
}
