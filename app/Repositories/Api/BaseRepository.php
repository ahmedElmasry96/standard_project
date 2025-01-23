<?php

namespace App\Repositories;

use App\Repositories\Api\Contracts\BaseRepositoryInterface;
use App\Http\Resources\BaseResource;
use Illuminate\Database\Eloquent\Model;

class BaseRepository implements BaseRepositoryInterface
{
    protected $model;
    protected $resourceClass;

    public function __construct(Model $model, $resourceClass = null)
    {
        $this->model = $model;
        $this->resourceClass = $resourceClass;
    }

    public function all($orderBy = 'DESC', $orderByKey = 'id' , $conditions = [], $paginate = false, $perPage = 15): BaseResource
    {
        $query = $this->model->orderBy($orderByKey, $orderBy);

        foreach ($conditions as $condition) {
            if (isset($condition['type']) && $condition['type'] === 'or') {
                $query->orWhere($condition['field'], $condition['operator'] ?? '=', $condition['value']);
            } else {
                $query->where($condition['field'], $condition['operator'] ?? '=', $condition['value']);
            }
        }

        if ($paginate) {
            $result = $query->paginate($perPage);
        } else {
            $result = $query->get();
        }

        return new BaseResource($result, $this->resourceClass);
    }

    public function find($id): ?BaseResource
    {
        $result = $this->model->findOrFail($id);
        return $result ? new BaseResource($result, $this->resourceClass) : null;
    }

    public function create(array $data): BaseResource
    {
        $result = $this->model->create($data);
        return new BaseResource($result, $this->resourceClass);
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
