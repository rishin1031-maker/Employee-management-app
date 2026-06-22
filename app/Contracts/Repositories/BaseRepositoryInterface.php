<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    public function all(array $relations = []): Collection;
    public function findById(int $id, array $relations = []): ?Model;
    public function findOrFail(int $id, array $relations = []): Model;
    public function create(array $data): Model;
    public function update(int $id, array $data): Model;
    public function delete(int $id): bool;
    public function paginate(int $perPage = 10, array $relations = []): LengthAwarePaginator;
}