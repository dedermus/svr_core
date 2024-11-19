<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Svr\Core\Models\SystemUsersNotifications;
use Svr\Core\Models\SystemUsersToken;
use Svr\Data\Models\DataCompanies;
use Svr\Data\Models\DataCompaniesLocations;
use Svr\Data\Models\DataUsersParticipations;
use Svr\Directories\Models\DirectoryCountriesRegion;

class UserNotificationsResource extends JsonResource
{
    public static $wrap = 'data';

    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $user_id = $request->only(['user_id']);
        $data = [
            'count_new' => SystemUsersNotifications::where([
                ['user_id', '=', $user_id],
                ['notification_date_view', '=', null]
            ])->count(),
            'count_total' => SystemUsersNotifications::where([
                ['user_id', '=', $user_id],
            ])->count(),
        ];
        return $data;
    }
}
