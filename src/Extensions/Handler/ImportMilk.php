<?php

namespace Svr\Core\Extensions\Handler;

use Illuminate\Support\Facades\Log;
use Svr\Core\Enums\ImportStatusEnum;
use Svr\Core\Enums\SystemTaskEnum;
use Svr\Core\Extensions\System\AnimalsImport;
use Svr\Core\Jobs\ProcessImportMilk;
use Svr\Raw\Models\FromSelexMilk;

class ImportMilk
{
    /**
     * Импорт молочного КСР из RAW в DATA
     *
     * @param $animal_id    - ID животного
     *
     * @return bool
     */
    public static function animalsImportMilk($animal_id): bool
    {
        // таблица data.data_animals
        // поля сопоставления (левая часть - поля для импорта / правая часть - поля назначения) для таблицы
        $matching_fields = [
            'nanimal'               => 'animal_nanimal',            // животное - НЕ уникальный идентификатор
            'nanimal_time'          => 'animal_nanimal_time',       // животное - уникальный идентификатор
            'guid_svr'              => 'animal_guid_self',          // гуид животного, который генерирует СВР в момент создания этой записи
            'ninv'					=> 'animal_code_inv_value',		// значение инвентарного номера животного
            'ngosregister'			=> 'animal_code_rshn_value',	// значение РСХН (УНСМ) номера животного
            'npol'                  => 'animal_sex_id',             // животное - код пола !!! сопоставляется через метод animal_import_sex_check
            'npor'                  => 'breed_id',                  // животное - код породы !!! сопоставление через метод animal_import_breed_get
            'mast'		            => 'animal_colour',             // животное - окрас
            'date_rogd'             => 'animal_date_birth',         // животное - дата рождения в формате YYYY.mm.dd
            'date_postupln'         => 'animal_date_income',        // животное - дата поступления в формате YYYY.mm.dd
            'nhoz_rogd'             => 'animal_place_of_birth_id',  // животное - хозяйство рождения (базовый индекс хозяйства)
            'nhoz_keep'             => 'animal_place_of_keeping_id',// животное - хозяйство содержания (базовый индекс хозяйства)
            'nhoz'                  => 'company_location_id',       // животное - базовый индекс хозяйства (текущее хозяйство)
            'task'                  => 'animal_task',               // код задачи берется из таблицы TASKS.NTASK (1 – молоко / 6- мясо / 4 - овцы)
            'ninv_otca'             => 'animal_father_inv',         // отец - инвентарный номер !!! сопоставление через метод animal_import_father_inv_check
            'ngosregister_otca'     => 'animal_father_rshn',        // отец - идентификационный номер РСХН
            'npor_otca'             => 'animal_father_breed_id',    // отец - код породы !!! сопоставление через метод animal_import_breed_get
            'date_rogd_otca'        => 'animal_father_date_birth',  // отец - дата рождения в формате YYYY.mm.dd
            'ninv_materi'           => 'animal_mother_inv',         // мать - инвентарный номер !!! сопоставление через метод animal_import_mother_inv_check
            'ngosregister_materi'   => 'animal_mother_rshn',        // мать - идентификационный номер РСХН
            'npor_materi'           => 'animal_mother_breed_id',    // мать - код породы !!! сопоставление через метод animal_import_breed_get
            'date_rogd_materi'      => 'animal_mother_date_birth',  // мать - дата рождения в формате YYYY.mm.dd
            'date_v'                => 'animal_out_date',           // животное - дата выбытия в формате YYYY.mm.dd
            'pv'                    => 'animal_out_reason',         // животное - причина выбытия
            'rashod'                => 'animal_out_rashod',         // животное - расход
            'gm_v'                  => 'animal_out_weight',         // животное - живая масса при выбытии (кг)
            'isp'                   => 'animal_breeding_value',     // животное - использование (племенная ценность)  ('UNDEFINED' - не определено, 'BREEDING' - Целевое, 'NON_BREEDING' - Пользовательное)
        ];

        $model = new FromSelexMilk();
        $field_primary_key = $model->getPrimaryKey();
        AnimalsImport::animal_import_worker($model, SystemTaskEnum::MILK->value, $matching_fields, $field_primary_key, $animal_id, 'import_milk');

        return true;
    }

    /**
     * Добавление животных молочного КРС в очередь по отправке на регистрацию
     *
     * @return bool
     */
    public static function addSendAnimalMilkQueue(): bool
    {
        $model = new FromSelexMilk();
        $date_new = new \DateTime();
        $date_new->modify('-1 day');
        $updated_at = $date_new->format('Y-m-d H:i:s');
        // получим список животных, которые застряли в статусе IN_PROGRESS более суток
        $animals_list_old_id = $model::where('import_status', ImportStatusEnum::IN_PROGRESS->value)
            ->where('task', SystemTaskEnum::MILK->value)
            ->where('updated_at', '<=', $updated_at)
            ->pluck($model->getPrimaryKey())
            ->map(fn($id) => (int) $id) // Преобразование в целое число
            ->all();

        Log::channel('import_milk')->info('Запустили метод добавления животных молочного КРС в очередь по отправке на регистрацию.');

        $animals_list_id = $model::where('import_status', ImportStatusEnum::NEW->value)
            ->where('task', SystemTaskEnum::MILK->value)
            ->pluck($model->getPrimaryKey())
            ->map(fn($id) => (int) $id) // Преобразование в целое число
            ->all();

        $animals_list_id = array_merge($animals_list_id, $animals_list_old_id);

        if($animals_list_id)
        {
            Log::channel('import_milk')->info('Пробуем добавить в очередь '.count($animals_list_id).' животных.');

            foreach($animals_list_id as $animal_id)
            {
                ProcessImportMilk::dispatch($animal_id)->onQueue(env('QUEUE_IMPORT_MILK', 'import_milk'));

                // обновим статус записи животного при импорте на статус - в прогрессе
                $model::where($model->getPrimaryKey(), $animal_id)
                    ->update(['import_status' => ImportStatusEnum::IN_PROGRESS->value]);
            }
            return true;
        }else{
            Log::channel('import_milk')->info('Животные для добавления не найдены.');

            return false;
        }
    }
}
