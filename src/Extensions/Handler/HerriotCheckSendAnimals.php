<?php

namespace Svr\Core\Extensions\Handler;

use Illuminate\Support\Facades\Log;
use Svr\Core\Extensions\Herriot\ApiHerriot;
use Svr\Core\Models\SystemUsersNotifications;
use Svr\Data\Models\DataAnimals;
use Svr\Data\Models\DataApplications;
use Svr\Data\Models\DataApplicationsAnimals;
use Svr\Data\Models\DataCompanies;
use Svr\Logs\Models\LogsHerriot;

class HerriotCheckSendAnimals
{
	public static function checkSendAnimal($application_animal_id)
	{
		$application_animal_data						= DataApplicationsAnimals::find($application_animal_id);

		if(empty($application_animal_data))
		{
			return false;
		}

		$animal_data									= DataAnimals::animalData($application_animal_data['animal_id'], $application_animal_data['application_id']);

		if (
			empty($animal_data['user_herriot_login']) ||
			empty($animal_data['user_herriot_password']) ||
			empty($animal_data['user_herriot_web_login']) ||
			empty($animal_data['user_herriot_apikey']) ||
			empty($animal_data['user_herriot_issuerid']) ||
			empty($animal_data['user_herriot_serviceid'])
		)
		{
			Log::channel('herriot_animals_check')->warning('Отправка животного на регистрацию. Не заданы реквизиты хорриот пользователя.');
			(new SystemUsersNotifications)->notificationsSendAdmin('Отправка животного на регистрацию. Не заданы логин или пароль хорриота пользователя. (HerriotSetAnimals.php)');
			return false;
		}

		//Экземпляр класса работы с API Хорриот
		$api							= new ApiHerriot($animal_data['user_herriot_login'], $animal_data['user_herriot_password']);
		$animal_send_log_data			= LogsHerriot::where('application_animal_id', '=', $application_animal_data['application_animal_id'])->first();

		if(empty($animal_send_log_data))
		{
			return false;
		}

		$response_from_herriot 			= $api->checkSendAnimall($animal_data['application_herriot_application_id'],
			$animal_data['user_herriot_apikey'],
			$animal_data['user_herriot_issuerid'],
			$animal_data['application_animal_id']);

		$application_animal_data->update([
			'application_animal_date_response' => date('Y-m-d H:i:s'),
			'application_animal_date_last_update' => date('Y-m-d H:i:s')
		]);

		$animal_send_log_data->update([
			'application_response_application_herriot' => $response_from_herriot
		]);

		if ($response_from_herriot === false)
		{
			$application_animal_data->update([
				'application_animal_status'		=> 'rejected'
			]);

			// TODO: нагуглить и изменить вставку текста ошибки запроса из Гузла
			$animal_send_log_data->update([
				'application_response_application_herriot' => $api->request_error()
			]);

			Log::channel('herriot_animals_check')->warning('Проверка статуса регистрации животного в Хорриот. Ничего не пришло из Хорриот. Животное '.$animal_data['animal_id']);
			(new SystemUsersNotifications)->notificationsSendAdmin('Проверка статуса регистрации животного в Хорриот. Ничего не пришло из Хорриот. Животное '.$animal_data['animal_id'].' (HerriotSetAnimals.php)');
			return false;
		}

		$error_data								= $api->errorParser($response_from_herriot);

		if ($error_data['error_status'] === true)
		{
			$application_animal_data->update([
				'application_herriot_check_text_error'         => $error_data['error_message'],
				'application_animal_status'                    => 'rejected',
			]);

			return false;
		}

		$registration_response_xml								= simplexml_load_string($response_from_herriot);

		if ($registration_response_xml === false)
		{
			$application_animal_data->update([
				'application_animal_status'						=> 'rejected',
			]);

			Log::channel('herriot_animals_check')->warning('Проверка статуса регистрации животного в Хорриот. Пришла ошибка из Хорриот. Животное '.$animal_data['animal_id']);
			(new SystemUsersNotifications)->notificationsSendAdmin('Проверка статуса регистрации животного в Хорриот. Пришла ошибка из Хорриот. Животное '.$animal_data['animal_id'].' (HerriotSetAnimals.php)');
			return false;
		}

		$registration_response_path								= $registration_response_xml->xpath("//soap:Body/*")[0];

		if (!isset($registration_response_path->application->status))
		{
			$application_animal_data->update([
				'application_animal_status'						=> 'rejected',
			]);

			return false;
		}

		if ((string)$registration_response_path->application->status == 'REJECTED')
		{
			$application_animal_data->update([
				'application_animal_status'						=> 'rejected',
			]);

			return false;
		}

		if ((string)$registration_response_path->application->status == 'ACCEPTED' || (string)$registration_response_path->application->status == 'IN_PROCESS')
		{
			return false;
		}

		if ((string)$registration_response_path->application->status == 'COMPLETED')
		{
			$hrt			= 'http://api.vetrf.ru/schema/cdm/herriot/applications/v1';
			$bs				= 'http://api.vetrf.ru/schema/cdm/base';
			$vd				= 'http://api.vetrf.ru/schema/cdm/mercury/vet-document/v2';

			$horriot_application_result			= $registration_response_path->application->result;
			$horriot_application_result_hrt		= $horriot_application_result->children($hrt)->registerAnimalResponse->animalRegistration;
			$horriot_application_result_bs		= $horriot_application_result_hrt->children($bs);
			$horriot_application_result_vd		= $horriot_application_result_hrt->children($vd);

			$guid = (string)$horriot_application_result_bs->guid;
			$registration_number = (string)$horriot_application_result_vd->registrationNumber;

			$application_animal_data->update([
				'application_animal_status'		=> 'registered',
			]);

			$animal_data->update([
				'animal_guid_horriot'			=> $guid,
				'animal_number_horriot'			=> $registration_number
			]);
		}

		// TODO: изобрести метод, который вытащит из базы компанию с нужным статусом и согласно сортировке и поставит ее в очередь
	}
}
