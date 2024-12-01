<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface UserRepositoryInterface
{
    public function createUser(array $data);
    public function getUserByEmail(string $email);
}
