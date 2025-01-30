<?php

namespace App\Repositories;

use App\Http\Resources\PaginatedResource;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\BaseResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
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

        $result = $paginate
            ? $query->paginate($perPage)
            : $query->get();

        return $paginate
            ? new PaginatedResource($result, $this->resourceClass)
            : new BaseResource($result, $this->resourceClass);
    }

    protected function buildQuery(string $orderBy, string $orderByKey, array $with, array $filters, ?string $search, array $searchColumns)
    {
        $query = $this->model->newQuery()->with($with);

        $query->orderBy($orderByKey, $orderBy);

        $this->applyFilters($query, $filters);
        $this->applySearch($query, $search, $searchColumns);

        return $query;
    }

    protected function applyFilters($query, array $filters): void
    {
        foreach ($filters as $filter) {
            $type = $filter['type'] ?? 'where';
            $field = $filter['field'] ?? null;
            $operator = $filter['operator'] ?? '=';
            $value = $filter['value'] ?? null;

            if (!$field) continue;

            $query->{$type}($field, $operator, $value);
        }
    }

    protected function applySearch($query, ?string $search, array $searchColumns): void
    {
        if ($search && $searchColumns) {
            $query->where(function ($q) use ($search, $searchColumns) {
                foreach ($searchColumns as $column) {
                    $q->orWhere($column, 'LIKE', "%{$search}%");
                }
            });
        }
    }

    public function find($id, array $with = []): BaseResource
    {
        $result = $this->model->with($with)->findOrFail($id);
        return new BaseResource($result, $this->resourceClass);
    }

    public function create(array $data): BaseResource
    {
        return DB::transaction(function () use ($data) {
            $result = $this->model->create($data);
            return new BaseResource($result->fresh(), $this->resourceClass);
        });
    }

    public function update($id, array $data): BaseResource
    {
        $model = $this->model->findOrFail($id);

        DB::transaction(function () use ($model, $data) {
            $model->update($data);
        });

        return new BaseResource($model->fresh(), $this->resourceClass);
    }

    public function delete($id): bool
    {
        $model = $this->model->findOrFail($id);
        return $model->delete();
    }
}
