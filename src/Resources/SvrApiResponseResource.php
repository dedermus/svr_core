<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class SvrApiResponseResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request|Collection $request
     *
     * @return array
     */
    public function toArray(Request|Collection $request): array
    {
        $returned_data = [
            'status'        => $this->resource['status'],
            'message'       => $this->resource['message'],
            'data'          => new $this->resource['response_resource_data']($this->resource),
            'notifications' => new SvrApiNotificationsResponseResource($this->resource['user_id']),
            'pagination'    => new SvrApiPaginationResponseResource($this->resource['pagination']),
        ];

        // При необходимости формируется секция dictionary
        if ($this->resource['response_resource_dictionary'] !== false) {
            $returned_data['dictionary'] = new $this->resource['response_resource_dictionary']($this->resource);
        }

        return $returned_data;
    }
}
