<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class NotificationsDataResource extends JsonResource
{
    public static $wrap = 'data';

    /**
     * Transform the resource collection into an array.
     *
     * @param Request|Collection $request
     *
     * @return array
     */
    public function toArray(Request|Collection $request): array
    {
        $notificationData = $this->resource['notification'];
        $returned_data = [];
        if (isset($notificationData['notification_id'])) {

            $returned_data = $this->getData($notificationData);
        }
        if (isset($notificationData[0]['notification_id'])) {

            foreach ($notificationData as $notification)
            {
                $returned_data[] = $this->getData($notification);
            }
        }
        return $returned_data;
    }

    /**
     * Format the date to the specified timezone and format.
     *
     * @param string|null $date
     *
     * @return string|null
     */
    private function formatDate(?string $date): ?string
    {
        return is_null($date)
            ? null
            : Carbon::parse($date)
                ->timezone(config('app.timezone'))
                ->format("Y-m-d H:i:s");
    }

    /**
     * Получить набор полей для ответа
     *
     * @param $notificationData
     *
     * @return array
     */
    private function getData($notificationData): array
    {
        return [
            'notification_id'        => $notificationData['notification_id'],
            'user_id'                => $notificationData['user_id'],
            'notification_type'      => $notificationData['notification_type'],
            'notification_title'     => $notificationData['notification_title'],
            'notification_text'      => $notificationData['notification_text'],
            'notification_date_add'  => $this->formatDate($notificationData['notification_date_add']),
            'notification_date_view' => $this->formatDate($notificationData['notification_date_view']),
        ];
    }
}
