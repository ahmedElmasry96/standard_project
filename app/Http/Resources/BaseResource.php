<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class BaseResource extends JsonResource
{
    protected $resourceClass;

    public function __construct($resource, $resourceClass = null)
    {
        parent::__construct($resource);
        $this->resourceClass = $resourceClass;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (!$this->resourceClass) {
            return parent::toArray($request);
        }

        if ($this->resource instanceof LengthAwarePaginator) {
            return [
                'data' => $this->resourceClass::collection($this->resource->items()),
                'pagination' => [
                    'total' => $this->resource->total(),
                    'per_page' => $this->resource->perPage(),
                    'current_page' => $this->resource->currentPage(),
                    'last_page' => $this->resource->lastPage(),
                    'from' => $this->resource->firstItem(),
                    'to' => $this->resource->lastItem(),
                ],
            ];
        }

        if (is_null($this->resource)) {
            return null;
        }

        return $this->resource instanceof \Illuminate\Database\Eloquent\Collection
            ? $this->resourceClass::collection($this->resource)
            : new $this->resourceClass($this->resource);
    }
}
