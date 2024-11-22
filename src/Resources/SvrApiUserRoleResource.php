<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiUserRoleResource extends JsonResource
{

    /**
     * Transform the resource collection into an array.
     *
     * @param Request|Collection $request
     * @return array
     */
    public function toArray(Request|Collection $request): array
    {
        $role_data = [
            'role_name_long' => $this->resource['role_name_long'],
            'role_name_short'=> $this->resource['role_name_short'],
            'role_id' => $this->resource['role_id'],
            'role_slug' => $this->resource['role_slug'],
            'role_status' => $this->resource['role_status'],
        ];

        if (isset($this->resource['user_role_id']))
        {
            $role_data['active'] = $this->resource['user_role_id'] == $this->resource['role_id'];
        }
        return $role_data;
    }
}
