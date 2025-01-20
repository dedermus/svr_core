<?php

namespace Svr\Core\Extensions\Handler;

use Illuminate\Support\Facades\Log;
use Svr\Core\Extensions\Herriot\ApiHerriot;
use Svr\Core\Jobs\ProcessHerriotCheckSendAnimals;
use Svr\Core\Models\SystemUsers;
use Svr\Core\Models\SystemUsersNotifications;
use Svr\Data\Models\DataAnimals;
use Svr\Data\Models\DataApplications;
use Svr\Data\Models\DataApplicationsAnimals;
use Svr\Data\Models\DataCompanies;
use Svr\Logs\Models\LogsHerriot;

class HerriotCheckSendAnimals
{
    /**
     * Метод добавления животных в очередь на проверку статуса регистрации
     * @return bool
     */
    public static function addCheckSendAnimalQueue()
    {
        Log::channel('herriot_animals_check')->info('Запустили метод добавления животных в очередь на проверку статуса регистрации.');

        $animals_list		= DataApplications::getAnimalByApplicationStatusAndAnimalStatus(['sent'], ['sent']);

        if($animals_list && is_array($animals_list) && count($animals_list) > 0)
        {
            Log::channel('herriot_animals_check')->info('Пробуем добавить в очередь '.count($animals_list).' животных.');

            foreach($animals_list as $animal_data)
            {
				DataApplicationsAnimals::find($animal_data->application_animal_id)->update([
					'application_animal_date_last_update' => date('Y-m-d H:i:s')
				]);

                ProcessHerriotCheckSendAnimals::dispatch((array)$animal_data)->onQueue(env('QUEUE_HERRIOT_CHECK_SEND_ANIMALS', 'herriot_check_send_animals'));
            }

            return true;
        }else{
            Log::channel('herriot_animals_check')->info('Животные для проверки регистрации не найдены.');

            return false;
        }
    }

	public static function checkSendAnimal($application_animal_data)
	{
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
			Log::channel('herriot_animals_check')->warning('Проверка регистрации животного. Не заданы реквизиты хорриот пользователя.');
			(new SystemUsersNotifications)->notificationsSendAdmin('Проверка регистрации животного. Не заданы логин или пароль хорриота пользователя. (HerriotCheckSendAnimals.php)');
			return false;
		}

		//Экземпляр класса работы с API Хорриот
		$api							= new ApiHerriot($doctor_data['user_herriot_login'], $doctor_data['user_herriot_password']);
		$animal_send_log_data			= LogsHerriot::where('application_animal_id', '=', $application_animal_data['application_animal_id'])->first();

		if(empty($animal_send_log_data))
		{
			return false;
		}

		$response_from_herriot 			= $api->checkSendAnimal($application_animal_data['application_herriot_application_id'],
			$doctor_data['user_herriot_apikey'],
			$doctor_data['user_herriot_issuerid'],
            $application_animal_data['application_animal_id']);

        DataApplicationsAnimals::find($application_animal_data['application_animal_id'])->update([
			'application_animal_date_response' => date('Y-m-d H:i:s'),
			'application_animal_date_last_update' => date('Y-m-d H:i:s')
		]);

		$animal_send_log_data->update([
			'application_response_application_herriot' => $response_from_herriot
		]);

		if ($response_from_herriot === false && $api->http_code() === false)
		{
            DataApplicationsAnimals::find($application_animal_data['application_animal_id'])->update([
				'application_animal_status'		=> 'rejected'
			]);

			$animal_send_log_data->update([
				'application_response_application_herriot' => $api->request_error()
			]);

			Log::channel('herriot_animals_check')->warning('Проверка статуса регистрации животного в Хорриот. Ничего не пришло из Хорриот. Животное '.$application_animal_data['animal_id']);
			(new SystemUsersNotifications)->notificationsSendAdmin('Проверка статуса регистрации животного в Хорриот. Ничего не пришло из Хорриот. Животное '.$application_animal_data['animal_id'].' (HerriotCheckSendAnimals.php)');
			return false;
		}
        if($response_from_herriot === false && $api->http_code() !== false)
        {
            $response_from_herriot = $api->request_error();
        }

		$error_data								= $api->errorParser($response_from_herriot);

		if ($error_data['error_status'] === true)
		{
            DataApplicationsAnimals::find($application_animal_data['application_animal_id'])->update([
				'application_herriot_check_text_error'         => $error_data['error_message'],
				'application_animal_status'                    => 'rejected',
			]);

            Log::channel('herriot_animals_check')->warning('Проверка статуса регистрации животного в Хорриот. Пришла ошибка из Хорриот. Животное '.$application_animal_data['animal_id']);

            return false;
		}

		$registration_response_xml								= simplexml_load_string($response_from_herriot);

		if ($registration_response_xml === false)
		{
            DataApplicationsAnimals::find($application_animal_data['application_animal_id'])->update([
				'application_animal_status'						=> 'rejected',
			]);

			Log::channel('herriot_animals_check')->warning('Проверка статуса регистрации животного в Хорриот. Пришла ошибка из Хорриот. Животное '.$application_animal_data['animal_id']);
			(new SystemUsersNotifications)->notificationsSendAdmin('Проверка статуса регистрации животного в Хорриот. Пришла ошибка из Хорриот. Животное '.$application_animal_data['animal_id'].' (HerriotCheckSendAnimals.php)');
			return false;
		}

		$registration_response_path								= $registration_response_xml->xpath("//soap:Body/*")[0];

		if (!isset($registration_response_path->application->status))
		{
            DataApplicationsAnimals::find($application_animal_data['application_animal_id'])->update([
				'application_animal_status'						=> 'rejected',
			]);

            Log::channel('herriot_animals_check')->warning('Проверка статуса регистрации животного в Хорриот. Нет статуса в ответе из Хорриот. Животное '.$application_animal_data['animal_id']);

            return false;
		}

		if ((string)$registration_response_path->application->status == 'REJECTED')
		{
            DataApplicationsAnimals::find($application_animal_data['application_animal_id'])->update([
				'application_animal_status'						=> 'rejected',
			]);

            Log::channel('herriot_animals_check')->warning('Проверка статуса регистрации животного в Хорриот. Отказ из Хорриот. Животное '.$application_animal_data['animal_id']);

            return false;
		}

		if ((string)$registration_response_path->application->status == 'ACCEPTED' || (string)$registration_response_path->application->status == 'IN_PROCESS')
		{
            Log::channel('herriot_animals_check')->warning('Проверка статуса регистрации животного в Хорриот. Еще переваривается в Хорриот. Животное '.$application_animal_data['animal_id']);

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

            DataApplicationsAnimals::find($application_animal_data['application_animal_id'])->update([
				'application_animal_status'		=> 'registered',
			]);

            DataAnimals::find($application_animal_data['animal_id'])->update([
				'animal_guid_horriot'			=> $guid,
				'animal_number_horriot'			=> $registration_number
			]);
		}
	}
}
