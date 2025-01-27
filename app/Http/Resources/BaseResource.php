<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource
{
    protected $resourceClass;

    /**
     * Create a new resource instance.
     *
     * @param mixed $resource
     * @param string|null $resourceClass
     */
    public function __construct($resource, $resourceClass = null)
    {
        parent::__construct($resource);
        $this->resourceClass = $resourceClass;
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        // If no specific resource class is provided, use the default transformation
        if (!$this->resourceClass) {
            return parent::toArray($request);
        }

        // Handle null resources
        if (is_null($this->resource)) {
            return null;
        }

        // Handle collections and individual resources
        return $this->resource instanceof \Illuminate\Database\Eloquent\Collection
            ? $this->resourceClass::collection($this->resource)
            : new $this->resourceClass($this->resource);
    }
}
