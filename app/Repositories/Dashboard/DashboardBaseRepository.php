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
     * Build a query with conditions and optional search.
     */
    protected function buildQuery($orderBy = 'DESC', $orderByKey = 'id', $conditions = [], $search = null, $searchColumns = [])
    {
        $query = $this->model->orderBy($orderByKey, $orderBy);

        // Apply conditions
        foreach ($conditions as $field => $value) {
            $query->where($field, $value);
        }

        // Apply search if search term and columns are provided
        if ($search && !empty($searchColumns)) {
            $query->where(function ($q) use ($search, $searchColumns) {
                foreach ($searchColumns as $column) {
                    $q->orWhere($column, 'LIKE', '%' . $search . '%');
                }
            });
        }

        return $query;
    }

    /**
     * Get all records with optional conditions and search.
     */
    public function all($orderBy = 'DESC', $orderByKey = 'id', $conditions = [], $search = null, $searchColumns = [])
    {
        $query = $this->buildQuery($orderBy, $orderByKey, $conditions, $search, $searchColumns);
        return $query->get();
    }

    /**
     * Paginate records with optional conditions and search.
     */
    public function paginate(
        $perPage = 15,
        $orderBy = 'DESC',
        $orderByKey = 'id',
        $conditions = [],
        $search = null,
        $searchColumns = []
    ): LengthAwarePaginator {
        $query = $this->buildQuery($orderBy, $orderByKey, $conditions, $search, $searchColumns);
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
