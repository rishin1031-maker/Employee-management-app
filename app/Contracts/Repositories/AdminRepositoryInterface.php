<?php

namespace App\Contracts\Repositories;

use App\Models\Admin;

interface AdminRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email): ?Admin;
}
