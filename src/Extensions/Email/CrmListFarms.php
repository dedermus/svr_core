<?php

namespace Svr\Core\Extensions\Email;

use Illuminate\Support\Facades\Http;

class CrmListFarms
{
    public static function getListFarms($token)
    {
        $response = Http::withUrlParameters([
            'host' => env('CRM_HOST'),
            'api' => env('CRM_API'),
            'endpoint' => env('CRM_END_POINT_FARMS'),
        ])->acceptJson()->post('{+host}/{api}/{endpoint}/', [
            'token' => $token
        ]);
        return true;
    }
}
