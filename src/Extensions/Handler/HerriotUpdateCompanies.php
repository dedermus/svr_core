<?php

namespace Svr\Core\Extensions\Handler;

use Illuminate\Support\Facades\Log;
use Svr\Core\Extensions\Herriot\ApiHerriot;
use Svr\Core\Models\SystemUsersNotifications;
use Svr\Data\Models\DataCompanies;

class HerriotUpdateCompanies
{
	//Определение namespaces в SOAP - ответе от API
	private static string $directory_namespace_data            = 'http://api.vetrf.ru/schema/cdm/dictionary/v2';
	private static string $directory_namespace_properties      = 'http://api.vetrf.ru/schema/cdm/base';


	public static function getCompanies($company_id)
	{
		$herriot_user			= env('HERRIOT_USER', false);
		$herriot_password		= env('HERRIOT_PASSWORD', false);

		if (!$herriot_user || !$herriot_password)
		{
			Log::channel('herriot_companies')->warning('Обновление компаний. Не заданы логин или пароль от хорриота.');
			(new SystemUsersNotifications)->notificationsSendAdmin('Обновление компаний из Хорриот. Не заданы логин или пароль от хорриота. (HerriotUpdateCompanies.php)');
			return false;
		}

		//Экземпляр класса работы с API Хорриот
		$api					= new ApiHerriot($herriot_user, $herriot_password);
		$company_data			= DataCompanies::find($company_id);

		if(empty($company_data))
		{
			return false;
		}

		$company_data->update(['updated_at' => date('Y-m-d H:i:s')]);

		$horriot_data = $api->getDirectoryOrganizationByInn($company_data['company_inn']);

		if($horriot_data === false || (is_array($horriot_data) && isset($horriot_data['error'])))
		{
			Log::channel('herriot_companies')->warning('Пришел HTML вместо XML (видимо косяк авторизации).', [$horriot_data]);
			(new SystemUsersNotifications)->notificationsSendAdmin('Пришел HTML вместо XML (видимо косяк авторизации). (HerriotUpdateCompanies.php)');
			return false;
		}

		$directory_xml = simplexml_load_string($horriot_data);

		if($directory_xml === false)
		{
			Log::channel('herriot_companies')->warning('Обновление компаний из Хорриот. Не удалось распарсить ответ из Хорриот.', [$horriot_data]);
			(new SystemUsersNotifications)->notificationsSendAdmin('Обновление компаний из Хорриот. Не удалось распарсить ответ из Хорриот. (HerriotUpdateCompanies.php)');
			return false;
		}

		$directory_path = $directory_xml->xpath("//soap:Body/*")[0];
		$directory_data = $directory_path->children("http://api.vetrf.ru/schema/cdm/dictionary/v2");

		$item = $directory_data->businessEntityList->businessEntity;

		if(!isset($item[0]))
		{
			$company_data->update(['company_status' => 'disabled']);
		}

		// костыль для похожих ИНН компаний разной блины
		foreach($directory_data->businessEntityList->businessEntity as $item)
		{
			$item_properties		= $item->children(self::$directory_namespace_properties);
			$item_data				= $item->children(self::$directory_namespace_data);
			$guid					= (string)$item_properties->guid;
			$item_inn				= (string)$item_properties->inn;

			if($item_inn == $company_data['company_inn'])
			{
				break;
			}
		}

		$company_data->update(['company_guid_vetis' => $guid, 'company_status' => 'enabled', 'company_status_horriot' => 'enabled']);

		// TODO: изобрести метод, который вытащит из базы компанию с нужным статусом и согласно сортировке и поставит ее в очередь
	}
}