<?php

namespace Svr\Core\Extensions\Handler;

use Illuminate\Support\Facades\Log;
use Svr\Core\Extensions\Herriot\ApiHerriot;
use Svr\Core\Models\SystemUsersNotifications;
use Svr\Data\Models\DataApplications;
use Svr\Data\Models\DataAnimals;
use Svr\Data\Models\DataApplicationsAnimals;
use Svr\Logs\Models\LogsHerriot;

class HerriotSendAnimals
{
	public static function sendAnimal($application_animal_id)
	{
		$application_animal_data						= DataApplicationsAnimals::find($application_animal_id);

		if(empty($application_animal_data))
		{
			return false;
		}

		$application_data								= DataApplications::find($application_animal_data['application_id']);
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
			Log::channel('herriot_animals_send')->warning('Отправка животного на регистрацию. Не заданы реквизиты хорриот пользователя.');
			(new SystemUsersNotifications)->notificationsSendAdmin('Отправка животного на регистрацию. Не заданы логин или пароль хорриота пользователя. (HerriotSetAnimals.php)');
			return false;
		}

		//Экземпляр класса работы с API Хорриот
		$api					= new ApiHerriot($animal_data['user_herriot_login'], $animal_data['user_herriot_password']);

		$application_data->update([
			'application_date_horriot' => date('Y-m-d H:i:s')
		]);

		$application_animal_data->update([
			'application_animal_date_horriot'			=> date('Y-m-d H:i:s'),
			'application_animal_date_last_update'		=> date('Y-m-d H:i:s')
		]);

		$response_from_herriot	= $api->sendAnimal(
			$application_animal_data,
			$application_animal_data['user_herriot_web_login'],
			$application_animal_data['user_herriot_apikey'],
			$application_animal_data['user_herriot_issuerid'],
			$application_animal_data['user_herriot_serviceid']
		);

		$animal_send_log_data	= LogsHerriot::where('application_animal_id', '=', $application_animal_data['application_animal_id'])->first();

		if(empty($animal_send_log_data))
		{
			return false;
		}

		//сохраняем ответ от Хорриот
		$animal_send_log_data->update(['application_response_herriot' => $response_from_herriot]);

		if($response_from_herriot === false)
		{
			//сохраняем ответ от Хорриот
			// TODO: нагуглить и изменить вставку текста ошибки запроса из Гузла
			$animal_send_log_data->update(['application_response_herriot' => $api->request_error()]);
			$application_animal_data->update(['application_animal_status' => 'rejected']);

			Log::channel('herriot_animals_send')->warning('Отправка животного на регистрацию в Хорриот. Ничего не пришло из Хорриот. Животное '.$application_animal_data['animal_id'].'.');
			(new SystemUsersNotifications)->notificationsSendAdmin('Отправка животного на регистрацию в Хорриот. Ничего не пришло из Хорриот. Животное '.$application_animal_data['animal_id'].'. (HerriotSetAnimals.php)');
			return false;
		}

		$error_data = $api->errorParser($response_from_herriot);

		if ($error_data['error_status'] === true)
		{
			$application_animal_data->update([
				'application_herriot_send_text_error'	=> $error_data['error_message'],
				'application_animal_status'				=> 'rejected'
			]);

			return false;
		}

		$registration_response_xml						= simplexml_load_string($response_from_herriot);

		if ($registration_response_xml === false)
		{
			$application_animal_data->update([
				'application_animal_status' => 'rejected'
			]);

			Log::channel('herriot_animals_send')->warning('Отправка животного на регистрацию в Хорриот. Пришла ошибка из Хорриот. Животное '.$application_animal_data['animal_id'].'.');
			(new SystemUsersNotifications)->notificationsSendAdmin('Отправка животного на регистрацию в Хорриот. Пришла ошибка из Хорриот. Животное '.$application_animal_data['animal_id'].'. (HerriotSetAnimals.php)');
			return false;
		}

		$registration_response_path						= $registration_response_xml->xpath("//soap:Body/*")[0];

		if (!isset($registration_response_path->application->status) || $registration_response_path->application->status != 'ACCEPTED')
		{
			Log::channel('herriot_animals_send')->warning('Статуса ваще нет, толи он не accepted. Животное '.$application_animal_data['animal_id'].'.');

			$application_animal_data->update([
				'application_animal_status'				 => 'rejected'
			]);

			return false;
		}

		if (!isset($registration_response_path->application->applicationId))
		{
			Log::channel('herriot_animals_send')->warning('applicationId нет. Животное '.$application_animal_data['animal_id'].'.');

			$application_animal_data->update([
				'application_animal_status' 			=> 'rejected'
			]);

			return false;
		}

		$applicationId = $registration_response_path->application->applicationId;

		$application_animal_data->update([
			'application_animal_status'					=> 'sent',
			'application_herriot_application_id'		=> $applicationId
		]);

		// TODO: изобрести метод, который вытащит из базы компанию с нужным статусом и согласно сортировке и поставит ее в очередь? или нет???
	}
}
