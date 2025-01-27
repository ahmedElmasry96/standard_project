<?php

namespace App\Repositories\Dashboard\Contracts;

interface DashboardBaseRepositoryInterface
{
    public function all($orderBy, $orderByKey, $conditions);

    public function paginate($perPage, $orderBy, $orderByKey, $conditions);

    public function find($id);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);
}
