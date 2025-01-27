<?php

namespace App\Repositories\Api;

use App\Http\Resources\PaginatedResource;
use App\Repositories\Api\Contracts\BaseRepositoryInterface;
use App\Http\Resources\BaseResource;
use Illuminate\Database\Eloquent\Model;

class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;
    protected $resourceClass;

    public function __construct(Model $model, $resourceClass = null)
    {
        $this->model = $model;
        $this->resourceClass = $resourceClass;
    }

    public function all($orderBy = 'DESC', $orderByKey = 'id', $filters = [], $search = null, $searchColumns = [], $paginate = false, $perPage = 15): BaseResource|PaginatedResource
    {
        $query = $this->model->orderBy($orderByKey, $orderBy);

        foreach ($filters as $field => $value) {
            $query->where($field, $value);
        }

        if ($search && count($searchColumns) > 0) {
            $query->where(function ($q) use ($search, $searchColumns) {
                foreach ($searchColumns as $column) {
                    $q->orWhere($column, 'LIKE', "%$search%");
                }
            });
        }

        $result = $paginate ? $query->paginate($perPage) : $query->get();

        if ($paginate) {
            return new PaginatedResource($result);
        }

        return new BaseResource($result, $this->resourceClass);    }

    public function find($id): ?BaseResource
    {
        $result = $this->model->findOrFail($id);
        return $result ? new BaseResource($result, $this->resourceClass) : null;
    }

    public function create(array $data): BaseResource
    {
        return \DB::transaction(function () use ($data) {
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
