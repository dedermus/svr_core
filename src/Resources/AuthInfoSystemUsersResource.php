<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Svr\Core\Models\SystemUsers;
use Svr\Core\Models\SystemUsersNotifications;
use Svr\Core\Models\SystemUsersRoles;
use Svr\Core\Models\SystemUsersToken;
use Svr\Data\Models\DataCompanies;
use Svr\Data\Models\DataCompaniesLocations;
use Svr\Data\Models\DataUsersParticipations;
use Svr\Directories\Models\DirectoryCountriesRegion;

class AuthInfoSystemUsersResource extends JsonResource
{
    public static $wrap = 'data';


//    public function notifications(Request $request)
//    {
//        $user_id = $request->only(['user_id']);
//        return [
//            'count_new' => SystemUsersNotifications::where([
//                ['user_id', '=', $user_id],
//                ['notification_date_view', '=', null]
//            ])->count(),
//            'count_total' => SystemUsersNotifications::where([
//                ['user_id', '=', $user_id],
//            ])->count(),
//        ];
//    }


    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $this->additional['status'] = true;
        $this->additional['message'] = 'Успешно';
        //$this->additional['notifications'] = $this->notifications($request);
        $this->additional['pagination'] = [
            "total_records" => 0,
            "max_page" => 1,
            "cur_page" => 1,
            "per_page" => 100
        ];



        
        
        
        


        $data = [
            'user_id' => $request->get('user_id'),
            'user_token' => $request->get('token_value'),
            'user_first' => $request->get('user_first'),
            'user_middle' => $request->get('user_middle'),
            'user_last' => $request->get('user_last'),
            'user_status' => $request->get('user_status'),
        ];
        $this->additional['data'] = (new SystemUsers())->getCurrentUserAvatar($request->get('user_id'));

        // коллекция привязок ролей к пользователю
        $user_roles_list = SystemUsersRoles::userRolesList($request->only(['user_id']));
        $this->additional['data']['user_roles_list'] = SystemUsersRoles::userRolesShort($user_roles_list);
        $this->additional['dictionary']['user_roles_list'] = SystemUsersRoles::userRolesLong($user_roles_list);

        // коллекция привязок компаний к пользователю
        $user_companies_locations_list = DataUsersParticipations::userCompaniesLocationsList($request->input('user_id'));
        $this->additional['data']['user_companies_locations_list'] = DataUsersParticipations::userCompaniesLocationsShort($user_companies_locations_list);
        $this->additional['dictionary']['user_companies_locations_list'] = DataUsersParticipations::userCompaniesLocationsLong($user_companies_locations_list);


        $this->additional['notifications'] = UserNotificationsResource::make($request);
        $this->additional['status'] = true;
        $this->additional['message'] = 'Успешно';
        $this->additional['pagination'] = [
            "total_records" => 1,
            "max_page" => 1,
            "cur_page" => 1,
            "per_page" => 1
        ];
        return $data;
    }
}
