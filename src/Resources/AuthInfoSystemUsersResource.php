<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Svr\Core\Models\SystemUsers;
use Svr\Core\Models\SystemUsersNotifications;
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

//        // Получить данные из Svr\Core\Models\SystemUsersToken
//        // TBL_USERS_TOKENS.' AS at ON at.token_id=(SELECT token_id FROM '.SCHEMA_SYSTEM.'.'.TBL_USERS_TOKENS.' WHERE user_id=a.user_id AND token_status = \'enabled\' ORDER BY token_last_action DESC LIMIT 1)
//        // !вернуть последнюю активную запись
//        $at = SystemUsersToken::where('user_id', $this->user_id)
//            ->where('token_status', 'enabled')
//            ->select('participation_id')
//            ->latest('token_last_action')->first();
//
//        // если нет активной записи, то вывести сообщение об ошибке и выйти
//        if (!$at) {
//            $this->additional['status'] = false;
//            $this->additional['message'] = 'У пользователя нет активной записи';
//            return ['user_data'=> null];
//        }
//
//        // Получить данные из Svr\Data\Models\DataUsersParticipations
//        // up ON up.participation_id = at.participation_id
//        $up = DataUsersParticipations::where('participation_id', $at->participation_id)
//            ->select('participation_item_id', 'role_id', 'participation_id')
//            ->first();
//
//        // Получить данные из Svr\Data\Models\DataCompaniesLocations
//        //.TBL_COMPANIES_LOCATIONS.' AS cl ON cl.company_location_id = up.participation_item_id
//        $cl = DataCompaniesLocations::where('company_location_id', $up->participation_item_id)
//            ->select('company_location_id', 'company_id', 'region_id', 'district_id')
//            ->first();
//
//        //  LEFT JOIN '.SCHEMA_DATA.'.'.TBL_COMPANIES.' AS c ON c.company_id = cl.company_id
//        $c = DataCompanies::where('company_id', $cl->company_id)
//            ->select('company_id', 'company_name_short', 'company_name_full', 'company_status')
//            ->first();
//
//        // LEFT JOIN '.SCHEMA_DIRECTORIES.'.'.TBL_COUNTRIES_REGIONS.' AS company_r ON company_r.region_id = cl.region_id
//        $company_r = DirectoryCountriesRegion::where('region_id', $cl->region_id)
//            ->select('region_id', 'region_name')
//            ->first();

        $data = [
            'user_id' => $request->get('user_id'),
            'user_token' => $request->get('token_value'),
            'user_first' => $request->get('user_first'),
            'user_middle' => $request->get('user_middle'),
            'user_last' => $request->get('user_last'),
            'user_status' => $request->get('user_status'),
        ];
        $this->additional['data'] = (new SystemUsers())->getCurrentUserAvatar($request->get('user_id'));
        $this->additional['data']['user_roles_list'] = UserRolesResource::make($request);
        $this->additional['dictionary']['user_roles_list'] = UserRolesListResource::make($request);

        $this->additional['data']['user_companies_locations_list'] = UserCompaniesLocationsResource::make($request);




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
