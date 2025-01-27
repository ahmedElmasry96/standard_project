<?php

namespace App\Repositories\Dashboard;

use App\Repositories\Dashboard\Contracts\DashboardBaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardBaseRepository implements DashboardBaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all records with optional conditions and ordering.
     */
    public function all($orderBy = 'DESC', $orderByKey = 'id', $conditions = [])
    {
        $query = $this->model->orderBy($orderByKey, $orderBy);

        foreach ($conditions as $field => $value) {
            $query->where($field, $value);
        }

        return $query->get();
    }

    /**
     * Paginate records with optional conditions and ordering.
     */
    public function paginate($perPage = 15, $orderBy = 'DESC', $orderByKey = 'id', $conditions = []): LengthAwarePaginator
    {
        $query = $this->model->orderBy($orderByKey, $orderBy);

        foreach ($conditions as $field => $value) {
            $query->where($field, $value);
        }

        return $query->paginate($perPage);
    }

    /**
     * Find a record by ID.
     */
    public function find($id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new record.
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update a record by ID.
     */
    public function update($id, array $data)
    {
        $record = $this->find($id);
        $record->update($data);

        return $record;
    }

    /**
     * Delete a record by ID.
     */
    public function delete($id)
    {
        $record = $this->find($id);
        return $record->delete();
    }
}
