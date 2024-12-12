<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class SvrApiUserSimpleResource extends JsonResource
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
			'user_id'							=> $this->resource['user_id'],
			'user_first'						=> $this->resource['user_first'],
			'user_middle'						=> $this->resource['user_middle'],
			'user_last'							=> $this->resource['user_last'],
			'user_avatar_small'					=> $this->resource['user_avatar_small'],
			'user_avatar_big'					=> $this->resource['user_avatar_big'],
			'user_status'						=> $this->resource['user_status'],
			'user_date_created'					=> strtotime($this->resource['user_date_created']) ? date("d.m.Y", strtotime($this->resource['user_date_created'])) : '',
			'user_date_block'					=> strtotime($this->resource['user_date_block']) ? date("d.m.Y", strtotime($this->resource['user_date_block'])) : '',
			'user_phone'						=> $this->resource['user_phone'],
			'user_email'						=> $this->resource['user_email'],
			'user_companies_count'				=> $this->resource['user_companies_count'],
			'user_herriot_data'					=> [
				'login'								=> empty($this->resource['user_herriot_login']) 	? '' : '******',
				'password'							=> empty($this->resource['user_herriot_password']) 	? '' : '******',
				'web_login'							=> empty($this->resource['user_herriot_web_login']) ? '' : '******',
				'apikey'							=> empty($this->resource['user_herriot_apikey']) 	? '' : '******',
				'issuerid'							=> empty($this->resource['user_herriot_issuerid']) 	? '' : '******',
				'serviceid'							=> empty($this->resource['user_herriot_serviceid']) ? '' : '******',
			],
			'user_companies_locations_list'		=> [],
			'user_roles_list'					=> [],
			'user_districts_list'				=> [],
			'user_regions_list'					=> [],
		];
    }
}
