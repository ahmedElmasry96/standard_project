<?php

namespace App\Repositories;

use App\Http\Resources\PaginatedResource;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\BaseResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;
    protected $resourceClass;

    public function __construct(Model $model, $resourceClass = null)
    {
        $this->model = $model;
        $this->resourceClass = $resourceClass;
    }

    public function all($orderBy = 'DESC', $with = [], $filters = [], $searchColumns = []): BaseResource|PaginatedResource
    {
        $paginate = request('pagination', false);
        $perPage = request('per_page', 15);
        $orderBy = request('order_by', $orderBy);
        $orderByKey = request('order_by_key', 'id');
        $search = request('search', false);

        $query = $this->buildQuery($orderBy, $orderByKey, $with, $filters, $search, $searchColumns);

        $result = $paginate ? $query->paginate($perPage) : $query->get();

        return $paginate ? new PaginatedResource($result, $this->resourceClass) : new BaseResource($result, $this->resourceClass);
    }

    /**
     * Build a query with filters, search, ordering, and relationships.
     */
    protected function buildQuery($orderBy, $orderByKey, $with, $filters, $search, $searchColumns)
    {
        $query = empty($with) ? $this->model->orderBy($orderByKey, $orderBy) : $this->model->with($with)->orderBy($orderByKey, $orderBy);

        // Apply filters (supports both 'where' and 'orWhere')
        foreach ($filters as $filter) {
            $type = $filter['type'] ?? 'where';
            $field = $filter['field'] ?? null;
            $operator = $filter['operator'] ?? '=';
            $value = $filter['value'] ?? null;

            if (!$field) continue; // Skip invalid filters

            $type === 'orWhere'
                ? $query->orWhere($field, $operator, $value)
                : $query->where($field, $operator, $value);
        }

        if ($search && !empty($searchColumns)) {
            $query->where(function ($q) use ($search, $searchColumns) {
                foreach ($searchColumns as $column) {
                    $q->orWhere($column, 'LIKE', "%$search%");
                }
            });
        }

        return $query;
    }
    public function find($id, $with = []): ?BaseResource
    {
        $query = empty($with) ? $this->model : $this->model->with($with);
        $result = $query->findOrFail($id);

        return $result ? new BaseResource($result, $this->resourceClass) : null;
    }

    public function create(array $data): BaseResource
    {
        return DB::transaction(function () use ($data) {
            $result = $this->model->create($data);
            return new BaseResource($result, $this->resourceClass);
        });
    }

    public function update($id, array $data): BaseResource
    {
        $record = $this->find($id);
        $record->update($data);
        $updated = $this->find($id);
        return new BaseResource($updated, $this->resourceClass);
    }

    public function delete($id)
    {
        $record = $this->find($id);
        return $record->delete();
    }
}
