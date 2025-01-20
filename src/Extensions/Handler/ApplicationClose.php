<?php

namespace Svr\Core\Extensions\Handler;

use Illuminate\Support\Facades\Log;
use Svr\Core\Jobs\ProcessApplicationClose;
use Svr\Data\Models\DataApplications;
use Svr\Data\Models\DataApplicationsAnimals;
use Illuminate\Support\Facades\DB;

class ApplicationClose
{
    /**
     * Метод добавления заявок в очередь на закрытие
     * @return bool
     */
    public static function addApplicationCloseQueue()
    {
        Log::channel('application_close')->info('Запустили метод закрытия заявок.');

        $application_data		= DataApplications::getApplicationDataByStatus(['sent', 'complete_full', 'complete_partial']);

        if($application_data && !is_null($application_data))
        {
            Log::channel('application_close')->info('Пробуем добавить заявку в очередь.');

            ProcessApplicationClose::dispatch($application_data->application_id)->onQueue(env('QUEUE_APPLICATION_CLOSE', 'application_close'));

            DataApplications::find($application_data->application_id)->update(['updated_at' => date('Y-m-d H:i:s')]);

            return true;
        }else{
            Log::channel('application_close')->info('Заявки для закрытия не найдены.');

            return false;
        }
    }

	public static function applicationClose($application_id)
	{
		$application_data	= DataApplications::find($application_id);
		$application_data->update(['updated_at' => date('Y-m-d H:i:s')]);

		$animals_count_total	= DB::table(DataApplicationsAnimals::getTableName())->where([
			'application_id'			=> $application_data['application_id']
		])->count();

		$animals_count_good		= DB::table(DataApplicationsAnimals::getTableName())->where([
			'application_id'			=> $application_data['application_id'],
			'application_animal_status'	=> 'registered'
		])->count();

		$animals_count_bad		= DB::table(DataApplicationsAnimals::getTableName())->where([
			'application_id'			=> $application_data['application_id'],
			'application_animal_status'	=> 'rejected'
		])->count();

		if($animals_count_total > 0 && $animals_count_total == ($animals_count_good + $animals_count_bad))
		{
			if($animals_count_bad > 0)
			{
				Log::channel('application_close')->warning('Частичная заявка, закрываем.');

				//(new SystemUsersNotifications)->notificationCreate('application_complete_partial', $application_data['company_id'], false, $application_data);

				$application_data->update([
					'application_status'			=> 'finished',
					'application_date_complete'		=> date('Y-m-d H:i:s')
				]);
			}else{
				Log::channel('application_close')->warning('Полная заявка, закрываем.');

				//(new SystemUsersNotifications)->notificationCreate('application_complete_full', $application_data['company_id'], false, $application_data);

				$application_data->update([
					'application_status'			=> 'finished',
					'application_date_complete'		=> date('Y-m-d H:i:s')
				]);
			}
		}

		if($animals_count_total == 0)
		{
			$application_data->update([
				'application_status'			=> 'finished'
			]);
		}
	}
}
