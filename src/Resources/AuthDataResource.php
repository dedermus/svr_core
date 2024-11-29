<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AuthDataResource extends JsonResource
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
            'user_id'                     => $this->getUserAttribute('user_id'),
            'user_first'                    => $this->getUserAttribute('user_first'),
            'user_last'                     => $this->getUserAttribute('user_last'),
            'user_middle'                 => $this->getUserAttribute('user_middle'),
            'user_avatar_small'             => $this->resource['avatars']['user_avatar_small'] ?? null,
            'user_avatar_big'             => $this->resource['avatars']['user_avatar_big'] ?? null,
            'user_status'                 => $this->getUserAttribute('user_status'),
            'user_date_created'             => $this->formatDate($this->getUserAttribute('created_at')),
            'user_date_block'             => $this->formatDate($this->getUserAttribute('user_date_block')),
            'user_phone'                    => $this->getUserAttribute('user_phone'),
            'user_email'                    => $this->getUserAttribute('user_email'),
            'user_companies_count'         => $this->resource['user_companies_count'] ?? null,
            'user_herriot_data'             => [
                'login'    => $this->maskSensitiveData($this->getUserAttribute('user_herriot_login')),
                'password' => $this->maskSensitiveData($this->getUserAttribute('user_herriot_password')),
            ],
            'user_companies_locations_list' => $this->pluckAttribute('user_companies_locations_list', 'company_location_id'),
            'user_roles_list'             => $this->pluckAttribute('user_roles_list', 'role_id'),
            'user_districts_list'         => $this->pluckAttribute('user_districts_list', 'district_id'),
            'user_regions_list'             => $this->pluckAttribute('user_regions_list', 'region_id'),
        ];
    }

    /**
     * Get user attribute safely.
     *
     * @param string $attribute
     * @return mixed|null
     */
    private function getUserAttribute(string $attribute)
    {
        return $this->resource['user'][$attribute] ?? null;
    }

    /**
     * Format date to the specified format.
     *
     * @param string|null $date
     * @return string|null
     */
    private function formatDate(?string $date): ?string
    {
        return $date ? Carbon::parse($date)->timezone(config('app.timezone'))->format("d.m.Y") : null;
    }

    /**
     * Mask sensitive data.
     *
     * @param string|null $data
     * @return string|null
     */
    private function maskSensitiveData(?string $data): ?string
    {
        return $data !== null ? "**************" : null;
    }

    /**
     * Pluck attribute from a collection.
     *
     * @param string $collectionKey
     * @param string $attribute
     *
     * @return Collection
     */
    private function pluckAttribute(string $collectionKey, string $attribute): Collection
    {
        return collect($this->resource[$collectionKey] ?? [])->pluck($attribute);
    }
}
