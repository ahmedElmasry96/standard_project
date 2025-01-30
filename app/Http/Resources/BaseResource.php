<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class BaseResource extends JsonResource
{
    protected $resourceClass;

    public function __construct($resource, $resourceClass = null)
    {
        parent::__construct($resource);
        $this->resourceClass = $resourceClass;
    }

    public function toArray($request): ?array
    {
        if (!$this->resourceClass) {
            return parent::toArray($request);
        }

        if ($this->resource instanceof Collection) {
            return $this->resourceClass::collection($this->resource)->toArray($request);
        }

        return (new $this->resourceClass($this->resource))->toArray($request);
    }
}
