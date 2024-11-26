<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class UserRolesResource extends JsonResource
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
            default => $this->full(),
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
            'role_name_long' => $this->role_name_long,
            'role_name_short' => $this->role_name_short,
            'role_id' => $this->role_id,
            'role_slug' => $this->role_slug,
            'role_status' => $this->role_status,
            'active' => $this->active ?? null,
        ];
    }

    public function full(): array
    {
        return [
            "role_id" => $this->role_id,
            "role_name_long" => $this->role_name_long,
            "role_name_short" => $this->role_name_short,
            "role_slug" => $this->role_slug,
            "role_status" => $this->role_status,
            "role_status_delete" => $this->role_status_delete,
            'active' => $this->active ?? null,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
