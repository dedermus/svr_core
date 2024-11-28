<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AuthDataResource extends JsonResource
{

    /**
     * Transform the resource collection into an array.
     *
     * @param Request|Collection $request
     * @return array
     */
    public function toArray(Request|Collection $request): array
    {
        $user_herriot_login = (isset($this->resource['user']['user_herriot_login'])) ?
            is_null($this->resource['user']['user_herriot_login'])
                ? null
                : "**************"
            : null;
        $user_herriot_password = (isset($this->resource['user']['user_herriot_password'])) ?
            is_null($this->resource['user']['user_herriot_password'])
                ? null
                : "**************"
            : null;
        $user_date_created = (isset($this->resource['user']['created_at'])) ?
            is_null($this->resource['user']['created_at'])
                ? null
                :  Carbon::parse($this->resource['user']['created_at'])->timezone(
                config('app.timezone')
            )->format("d.m.Y")
            : null;
        $user_date_block = (isset($this->resource['user']['user_date_block'])) ?
            is_null($this->resource['user']['user_date_block'])
                ? null
                :  Carbon::parse($this->resource['user']['user_date_block'])->timezone(
                config('app.timezone')
            )->format("d.m.Y")
            : null;
        return [
            'user_id'                       => $this->resource['user']['user_id'] ?? null,
            'user_first'                    => $this->resource['user']['user_first'] ?? null,
            'user_last'                     => $this->resource['user']['user_last'] ?? null,
            'user_middle'                   => $this->resource['user']['user_middle'] ?? null,
            'user_avatar_small'             => $this->resource['avatars']['user_avatar_small'],
            'user_avatar_big'               => $this->resource['avatars']['user_avatar_big'],
            'user_status'                   => $this->resource['user']['user_status'] ?? null,
            'user_date_created'             => $user_date_created,
            'user_date_block'               => $user_date_block,
            'user_phone'                    => $this->resource['user']['user_phone'] ?? null,
            'user_email'                    => $this->resource['user']['user_email'] ?? null,
            'user_companies_count'          => $this->resource['user_companies_count'] ?? null,
            'user_herriot_data'             => [
                'login'    => $user_herriot_login,
                'password' => $user_herriot_password,
            ],
            'user_companies_locations_list' => collect($this->resource['user_companies_locations_list'])->pluck(
                'company_location_id'
            ),
            'user_roles_list'               => collect($this->resource['user_roles_list'])->pluck('role_id'),
            'user_districts_list'           => collect($this->resource['user_districts_list'])->pluck('district_id'),
            'user_regions_list'             => collect($this->resource['user_regions_list'])->pluck('region_id'),
        ];
    }
}
