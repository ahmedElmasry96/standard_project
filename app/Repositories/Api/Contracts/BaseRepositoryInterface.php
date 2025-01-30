<?php

namespace App\Repositories\Contracts;

use App\Http\Resources\BaseResource;
use App\Http\Resources\PaginatedResource;

interface BaseRepositoryInterface
{
    public function all(
        string $orderBy = 'DESC',
        array $with = [],
        array $filters = [],
        array $searchColumns = [],
    ): BaseResource|PaginatedResource;

    public function find($id, array $with = []): BaseResource;
    public function create(array $data): BaseResource;
    public function update($id, array $data): BaseResource;
    public function delete($id): bool;
}
