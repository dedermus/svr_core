<?php

namespace Svr\Core\Extensions\Email;

use Illuminate\Support\Facades\Http;

class CrmAuth
{
    public function getToken($email, $password)
    {
        $recorded = Http::recorded();
        // Basic authentication...


        $response = Http::withUrlParameters([
            'host' => env('CRM_HOST'),
            'api' => env('CRM_API'),
            'endpoint' => env('CRM_END_POINT_TOKEN'),
        ])->acceptJson()->post('{+host}/{api}/{endpoint}/', [
            'email' => $email, 'password' => $password
        ]);

//        $response = Http::acceptJson()->post('https://crm.plinor.local/allApi/getToken/', [
//            'email' => $email, 'password' => $password
//        ]);
        $response = json_decode($response->body(), true);
        if ($response['data']['token']) {
            var_export($response['data']['token']); die();
        }
    }
}
