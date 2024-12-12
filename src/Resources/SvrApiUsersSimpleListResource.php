<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

use Svr\Core\Resources\SvrApiUserSimpleResource;

class SvrApiUsersSimpleListResource extends JsonResource
{
    /**
     * Указывает, следует ли сохранить ключи коллекции ресурса.
     *
     * @var bool
     */
    public $preserveKeys = true;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request|Collection $request
     * @return array
     */
    public function toArray(Request|Collection $request): array
    {
        $returned_data = [];

		if($this->resource && is_array($this->resource) && count($this->resource) > 0)
		{
			foreach ($this->resource as $value)
			{
				$returned_data[$value['user_id']] = new SvrApiUserSimpleResource(collect($value));
			}
		}

        return $returned_data;
    }
}
