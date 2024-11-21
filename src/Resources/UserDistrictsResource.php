<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Svr\Core\Models\SystemUsersNotifications;
use Svr\Core\Models\SystemUsersRoles;
use Svr\Core\Models\SystemUsersToken;
use Svr\Data\Models\DataCompanies;
use Svr\Data\Models\DataCompaniesLocations;
use Svr\Data\Models\DataUsersParticipations;
use Svr\Directories\Models\DirectoryCountriesRegion;

class UserDistrictsResource extends JsonResource
{
    public static $wrap = 'data';

    protected string $widget;

    /**
     * @param        $resource
     * @param string $widget - Виджеты: simple | full
     */
    public function __construct($resource, string $widget = 'simple')
    {
        parent::__construct($resource);
        $this->widget = $widget;
    }


    public function toArray($request)
    {
        return match ($this->widget) {
            'simple' => $this->simple(),
            'full' => $this->full(),
            default => $this->simple(),
        };
    }

    /**
     * Делаем кастомный метод collection для ресурса.
     * Используем если не хотим создавать отдельный класс коллекций ресурса.
     *
     * @param        $resource
     * @param string $widget - Виджеты: simple | full
     * @return mixed
     */
    public static function customCollection($resource, $widget = 'simple'): mixed
    {
        return $resource->map(function ($item) use ($widget) {
            return new static($item, $widget);
        });
    }

    public function simple(): array
    {
        return [
            'district_id'			=> $this->district_id,
			'district_name'			=> $this->district_name,
			'active'				=> $this->active ?? null,
        ];
    }

    public function full(): array
    {
        return [
            'district_id' => $this->district_id,
        ];
    }
}
