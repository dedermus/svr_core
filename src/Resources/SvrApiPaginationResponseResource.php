<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class SvrApiPaginationResponseResource extends JsonResource
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
        $max_page = ceil($this->frac(Config::get('total_records'), Config::get('per_page')));
        $cur_page = Config::get('cur_page', env('CUR_PAGE'));
        return [
            'total_records' => Config::get('total_records', env('TOTAL_RECORDS')),
            'per_page'      => Config::get('per_page', env('PER_PAGE')),
            'cur_page'      => $cur_page,
            'max_page'      => ceil($this->frac(Config::get('total_records'), Config::get('per_page'))),
        ];
    }

    /**
     * Делит первый аргумент на второй.
     * Если второй аргумент равен нулю, то возвращает ноль.
     *
     * @param $dividend
     * @param $divider
     *
     * @return float|int
     */
    private function frac($dividend, $divider): float|int
    {
        if (!$divider) {
            return 0;
        }

        if ((float)$divider == 0) {
            return 0;
        }

        return $dividend / $divider;
    }
}
