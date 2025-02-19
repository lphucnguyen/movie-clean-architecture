<?php

namespace App\Application\DTOs\User;

use App\Shared\Application\DTOs\BaseDTO;

class UpdateUserDTO extends BaseDTO
{
    public string $first_name;
    public string $last_name;
    public string $email;
    public string $username;
    public string|null $password;
    public string|null $avatar;
}
