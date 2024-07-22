<?php

declare(strict_types=1);

namespace App\Domain\DTO\User;

class UpdateUserData
{

    public function __construct(
        public string $email,
        public string $INN,
        public string $first_name,
        public string $last_name,
        public ?string $patronymic,
        public ?string $birthday,
        public ?string $person_phone,
        public ?string $work_phone,
    ) {}

}