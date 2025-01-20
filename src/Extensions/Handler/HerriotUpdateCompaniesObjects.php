<?php

namespace Svr\Core\Extensions\Handler;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Svr\Core\Enums\SystemStatusEnum;
use Svr\Core\Extensions\Herriot\ApiHerriot;
use Svr\Core\Jobs\ProcessHerriotUpdateCompanies;
use Svr\Core\Jobs\ProcessHerriotUpdateCompaniesObjects;
use Svr\Core\Models\SystemUsersNotifications;
use Svr\Data\Models\DataCompanies;
use Svr\Data\Models\DataCompaniesObjects;

/**
 * Обработчик обновления поднадзорных объектов
 */
class HerriotUpdateCompaniesObjects
{
    //Определение namespaces в SOAP - ответе от API
    private static string $directory_namespace_data            = 'http://api.vetrf.ru/schema/cdm/dictionary/v2';
    private static string $directory_namespace_properties      = 'http://api.vetrf.ru/schema/cdm/base';

    /**
     * Метод добавления компаний в очередь на обновление
     * @return bool
     */
    public static function addCompanyObjectsQueue()
    {
        Log::channel('herriot_companies_objects')->info('Запустили метод добавления организации в очередь на обновление.');

        $company = DataCompanies::whereNotNull('company_guid_vetis')
            ->where('company_status', '=', SystemStatusEnum::ENABLED->value)
            ->orderBy('company_objects_offset', 'desc')
            ->orderBy('company_date_update_objects', 'asc')
            ->orderBy('updated_at', 'asc')
            ->first();

        if($company)
        {
            Log::channel('herriot_companies_objects')->info('Пробуем добавить в очередь компанию: '.($company['company_id']));

			DataCompanies::find($company['company_id'])->update(['company_date_update_objects' => date('Y-m-d H:i:s')]);

            ProcessHerriotUpdateCompaniesObjects::dispatch($company['company_id'])->onQueue(env('QUEUE_HERRIOT_COMPANIES_OBJECTS', 'herriot_companies_objects'));

            return true;
        }else{
            Log::channel('herriot_companies_objects')->info('Компаний для обновления нет.');

            return false;
        }
    }

    public static function getCompanyObjects($company_id)
    {
        $herriot_user			= env('HERRIOT_USER', false);
        $herriot_password		= env('HERRIOT_PASSWORD', false);

        if (!$herriot_user || !$herriot_password)
        {
            Log::channel('herriot_companies_objects')->warning('Обновление поднадзорных объектов. Не заданы логин или пароль от хорриота.');
            (new SystemUsersNotifications)->notificationsSendAdmin('Обновление компаний из Хорриот. Не заданы логин или пароль от хорриота. (HerriotUpdateCompaniesObjects.php)');
            return false;
        }

        //Экземпляр класса работы с API Хорриот
        $api					= new ApiHerriot($herriot_user, $herriot_password);
        $company_data			= DataCompanies::find($company_id);

        if(empty($company_data))
        {
            return false;
        }

        $offset					= 0; //$company_data['company_objects_offset'];
        $count                  = 1000;
        $total					= self::companyObjectAction($api, $company_data, $count, $offset);

        if((($total - $offset) - $count) > $count)
        {
            $iterations			= ceil((($total - $offset)) / $count);

            for($i = 1; $i <= $iterations; $i++)
            {
                $offset			+= $count;
                $total			= self::companyObjectAction($api, $company_data, $count, $offset);

                sleep(1); //TODO: нужно ли нам спать или только работать?
            }

            //$company_data->update(['company_objects_offset' => $offset]);
        } else {
            //$company_data->update(['company_objects_offset' => 0]);
        }
    }


    /**
     * Функция для получения пачки поднадзорных объектов из хорриота
     * @param $api
     * @param $company_data
     * @param $count
     * @param $offset
     * @return false|string
     */
    private static function companyObjectAction($api, $company_data, $count = 1000, $offset = 0): false|string
    {
        $horriot_data									= $api->getCompanyObjectsByGuid($company_data['company_guid_vetis'], $count, $offset);

        if($horriot_data === false || (is_array($horriot_data) && isset($horriot_data['error'])))
        {
            Log::channel('herriot_companies_objects')->warning('Пришел HTML вместо XML (видимо косяк авторизации).', [$horriot_data]);
            (new SystemUsersNotifications)->notificationsSendAdmin('Пришел HTML вместо XML (видимо косяк авторизации). (HerriotUpdateCompaniesObjects.php)');
            return false;
        }

        $directory_xml								= simplexml_load_string($horriot_data);
        if ($directory_xml === false)
        {
            Log::channel('herriot_companies_objects')->warning('Обновление поднадзорных объектов из Хорриот. Не удалось распарсить ответ из Хорриот.', [$horriot_data]);
            (new SystemUsersNotifications)->notificationsSendAdmin('Обновление поднадзорных объектов из Хорриот. Не удалось распарсить ответ из Хорриот. (HerriotUpdateCompaniesObjects.php)');
            return false;
        }
        $directory_path								= $directory_xml->xpath("//soap:Body/*")[0];
        $directory_data								= $directory_path->children("http://api.vetrf.ru/schema/cdm/dictionary/v2");

        foreach ($directory_data->supervisedObjectList->supervisedObject as $item)
        {
            $item_properties = $item->children(self::$directory_namespace_properties);

            if ((bool)$item_properties->active === false) continue;

            $item_data					= $item->children(self::$directory_namespace_data);
            $guid_object				= (string)$item_properties->guid;
            $approval_number			= (string)$item_data->approvalNumber;
            $address_view				= (string)$item_data->enterprise->address->addressView;

            $company_object = DataCompaniesObjects::where(['company_object_guid_horriot' => $guid_object, 'company_id' => $company_data['company_id']])->first();

            if($company_object)
            {
                $company_object->update([
                    'company_object_approval_number'    => $approval_number,
                    'company_object_address_view'       => $address_view,
                    'updated_at'                        => date('Y-m-d H:i:s')
                ]);
            }else{
                (new DataCompaniesObjects)->fill([
                    'company_id'                        => $company_data['company_id'],
                    'company_object_guid_self'          => Str::uuid(),
                    'company_object_guid_horriot'       => $guid_object,
                    'company_object_approval_number'    => $approval_number,
                    'company_object_address_view'       => $address_view,
                    'created_at'                        => date('Y-m-d H:i:s')
                ])->save();
            }

            $company_data->update(['company_date_update_objects' => date('Y-m-d H:i:s')]);
        }

        return (string)$directory_data->supervisedObjectList->attributes()['total'];
    }
}


