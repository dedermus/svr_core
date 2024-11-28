<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiUserRolesListResource extends JsonResource
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
    public function toArray(Request|Collection $request):array
    {
        $returned_data = [];
        foreach ($this->resource['user_roles_list'] as $value)
        {
            if (isset($this->resource['user_participation_info']['role_id'])) {
                $value->user_role_id = $this->resource['user_participation_info']['role_id'];
                $returned_data[$value->role_id] = new SvrApiUserRoleResource(collect($value));
            }
        }
        return $returned_data;
    }
}
