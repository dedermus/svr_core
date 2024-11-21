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
            'user_roles_list' => collect($this->resource['user_roles_list'])->pluck('role_id'),
        ];

        // коллекция привязок ролей к пользователю
        $this->additional['dictionary']['user_roles_list'] = collect($this->resource['user_roles_list'])->keyBy('role_id');

        // коллекция привязок компаний к пользователю
//        $user_companies_locations_list = DataUsersParticipations::userCompaniesLocationsList($request->input('user_id'));
//        $this->additional['data']['user_companies_locations_list'] = DataUsersParticipations::userCompaniesLocationsShort($user_companies_locations_list);
//        $this->additional['dictionary']['user_companies_locations_list'] = DataUsersParticipations::userCompaniesLocationsLong($user_companies_locations_list);

        $this->additional['notifications'] = $this->resource['notifications'];
        $this->additional['pagination'] = $this->resource['pagination'];

        return $data;
    }
}
