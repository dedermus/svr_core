<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class AuthInfoSystemUsersResource extends JsonResource
{
    public static $wrap = 'data';

    /**
     * Transform the resource collection into an array.
     *
     * @param Request|Collection $request
     * @return array
     */
    public function toArray(Request|Collection $request): array
    {
        $this->additional['status'] = $this->resource['status'];
        $this->additional['message'] = $this->resource['message'];

        $data = [
            'user_id' => $this->resource['user']['user_id'],
            'user_token' => $this->resource['user_token'],
            'user_first' => $this->resource['user']['user_first'],
            'user_middle' => $this->resource['user']['user_middle'],
            'user_last' => $this->resource['user']['user_last'],
            'user_avatar_small' => $this->resource['avatars']['user_avatar_small'],
            'user_avatar_big' => $this->resource['avatars']['user_avatar_big'],
            // списки id
            'user_companies_locations_list' => collect($this->resource['user_companies_locations_list'])->pluck('participation_id'),
            'user_roles_list' => collect($this->resource['user_roles_list'])->pluck('role_id'),
            'user_districts_list' => collect($this->resource['user_districts_list'])->pluck('participation_id'),
            'user_regions_list' => collect($this->resource['user_regions_list'])->pluck('participation_id'),
        ];

        // справочники
        // коллекция привязок компаний к пользователю
        $this->additional['dictionary']['user_companies_locations_list'] = UserCompaniesLocationsResource::customCollection($this->resource['user_companies_locations_list'], 'simple')
            ->map(function ($resource) {
                // Преобразует каждый ресурс в массив
                return $resource->resolve();
            })
            // Группируем элементы коллекции по ключу role_id
            ->keyBy('company_location_id');

        // коллекция привязок ролей к пользователю
        $this->additional['dictionary']['user_roles_list'] = UserRolesResource::customCollection($this->resource['user_roles_list'], 'simple')
            ->map(function ($resource) {
                // Преобразует каждый ресурс в массив
                return $resource->resolve();
            })
            // Группируем элементы коллекции по ключу role_id
            ->keyBy('role_id');

        // коллекция привязок ролей к пользователю
        $this->additional['dictionary']['user_districts_list'] = UserDistrictsResource::customCollection($this->resource['user_districts_list'], 'simple')
            ->map(function ($resource) {
                // Преобразует каждый ресурс в массив
                return $resource->resolve();
            })
            // Группируем элементы коллекции по ключу role_id
            ->keyBy('district_id');


        $this->additional['notifications'] = $this->resource['notifications'];
        $this->additional['pagination'] = $this->resource['pagination'];

        return $data;
    }
}
