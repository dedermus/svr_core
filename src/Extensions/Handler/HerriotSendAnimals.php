<?php

namespace Svr\Core\Extensions\Handler;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use Svr\Core\Extensions\Herriot\ApiHerriot;
use Svr\Core\Models\SystemUsers;
use Svr\Core\Models\SystemUsersNotifications;
use Svr\Data\Models\DataApplications;
use Svr\Data\Models\DataAnimals;
use Svr\Data\Models\DataApplicationsAnimals;
use Svr\Logs\Models\LogsHerriot;

use Svr\Core\Jobs\ProcessHerriotSendAnimals;

class HerriotSendAnimals
{
	/**
	 * Метод добавления животных в очередь на отправку на регистрацию
	 * @return bool
	 */
	public static function addSendAnimalQueue()
	{
		Log::channel('herriot_animals_send')->info('Запустили метод добавления животных в очередь на отправку на регистрацию.');

		$animals_list		= DataApplications::getAnimalByApplicationStatusAndAnimalStatus(['sent'], ['in_application']);

		if($animals_list && is_array($animals_list) && count($animals_list) > 0)
		{
			Log::channel('herriot_animals_send')->info('Пробуем добавить в очередь '.count($animals_list).' животных.');

			foreach($animals_list as $animal_data)
			{
				ProcessHerriotSendAnimals::dispatch((array)$animal_data)->onQueue(env('QUEUE_HERRIOT_SEND_ANIMALS', 'herriot_send_animals'));
			}

			return true;
		}else{
			Log::channel('herriot_animals_send')->info('Животные для добавления не найдены.');

			return false;
		}
	}


    /**
	 * Непосредственная отправка животного
     * @throws ConnectionException
     */
    public static function sendAnimal($application_animal_data)
	{
//		$application_animal_data						= $application_animal_data->toArray();
//
//		if(empty($application_animal_data))
//		{
//			return false;
//		}

		$application_data								= DataApplications::find($application_animal_data['application_id']);
//		$animal_data									= DataAnimals::animalData($application_animal_data['animal_id'], $application_animal_data['application_id']);

        $doctor_data                                    = SystemUsers::find($application_animal_data['doctor_id']);

		if (
			empty($doctor_data['user_herriot_login']) ||
			empty($doctor_data['user_herriot_password']) ||
			empty($doctor_data['user_herriot_web_login']) ||
			empty($doctor_data['user_herriot_apikey']) ||
			empty($doctor_data['user_herriot_issuerid']) ||
			empty($doctor_data['user_herriot_serviceid'])
		)
		{
			Log::channel('herriot_animals_send')->warning('Отправка животного на регистрацию. Не заданы реквизиты хорриот пользователя.');
			(new SystemUsersNotifications)->notificationsSendAdmin('Отправка животного на регистрацию. Не заданы логин или пароль хорриота пользователя. (HerriotSetAnimals.php)');
			return false;
		}

		//Экземпляр класса работы с API Хорриот
		$api					= new ApiHerriot($doctor_data['user_herriot_login'], $doctor_data['user_herriot_password']);

		$application_data->update([
			'application_date_horriot' => date('Y-m-d H:i:s')
		]);

		DataApplicationsAnimals::find($application_animal_data['application_animal_id'])->update([
			'application_animal_date_horriot'			=> date('Y-m-d H:i:s'),
			'application_animal_date_last_update'		=> date('Y-m-d H:i:s')
		]);
		$response_from_herriot	= $api->sendAnimal(
			$application_animal_data,
			$doctor_data['user_herriot_web_login'],
			$doctor_data['user_herriot_apikey'],
			$doctor_data['user_herriot_issuerid'],
			$doctor_data['user_herriot_serviceid']
		);
		$animal_send_log_data	= LogsHerriot::where('application_animal_id', '=', $application_animal_data['application_animal_id'])->first();

		if(empty($animal_send_log_data))
		{
			return false;
		}

		//сохраняем ответ от Хорриот
		$animal_send_log_data->update(['application_response_herriot' => $response_from_herriot]);

		if($response_from_herriot === false && $api->http_code() === false)
		{
            //сохраняем ответ от Хорриот
            $animal_send_log_data->update(['application_response_herriot' => $api->request_error()]);
            DataApplicationsAnimals::find($application_animal_data['application_animal_id'])->update(['application_animal_status' => 'rejected']);

            Log::channel('herriot_animals_send')->warning('Отправка животного на регистрацию в Хорриот. Ничего не пришло из Хорриот. Животное '.$application_animal_data['animal_id'].'.');
            (new SystemUsersNotifications)->notificationsSendAdmin('Отправка животного на регистрацию в Хорриот. Ничего не пришло из Хорриот. Животное '.$application_animal_data['animal_id'].'. (HerriotSetAnimals.php)');
            return false;
		}
        if($response_from_herriot === false && $api->http_code() !== false)
        {
            $response_from_herriot = $api->request_error();
        }

		$error_data = $api->errorParser($response_from_herriot);

		if ($error_data['error_status'] === true)
		{
            DataApplicationsAnimals::find($application_animal_data['application_animal_id'])->update([
				'application_herriot_send_text_error'	=> $error_data['error_message'],
				'application_animal_status'				=> 'rejected'
			]);

            Log::channel('herriot_animals_send')->warning('Отправка животного на регистрацию в Хорриот. Пришла ошибка из Хорриот. Животное '.$application_animal_data['animal_id'].'.');

            return false;
		}

		$registration_response_xml						= simplexml_load_string($response_from_herriot);

		if ($registration_response_xml === false)
		{
            DataApplicationsAnimals::find($application_animal_data['application_animal_id'])->update([
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

            DataApplicationsAnimals::find($application_animal_data['application_animal_id'])->update([
				'application_animal_status'				 => 'rejected'
			]);

			return false;
		}

		if (!isset($registration_response_path->application->applicationId))
		{
			Log::channel('herriot_animals_send')->warning('applicationId нет. Животное '.$application_animal_data['animal_id'].'.');

            DataApplicationsAnimals::find($application_animal_data['application_animal_id'])->update([
				'application_animal_status' 			=> 'rejected'
			]);

			return false;
		}

		$applicationId = $registration_response_path->application->applicationId;

        DataApplicationsAnimals::find($application_animal_data['application_animal_id'])->update([
			'application_animal_status'					=> 'sent',
			'application_herriot_application_id'		=> $applicationId
		]);
	}
}
