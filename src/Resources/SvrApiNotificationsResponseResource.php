<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Svr\Core\Models\SystemUsersNotifications;


class SvrApiNotificationsResponseResource extends JsonResource
{

    /**
     * Transform the resource collection into an array.
     *
     * @param Request|Collection $request
     * @return array
     */
    public function toArray(Request|Collection $request): array
    {
        return [
            'count_new' => SystemUsersNotifications::where([
                ['user_id', '=', $this->resource],
                ['notification_date_view', '=', null]
            ])->count(),
            'count_total' => SystemUsersNotifications::where([
                ['user_id', '=', $this->resource],
            ])->count(),
        ];
    }
}
