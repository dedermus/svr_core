<?php

namespace Svr\Core\Extensions\Handler;

use Illuminate\Support\Facades\Log;
use Svr\Core\Enums\ImportStatusEnum;
use Svr\Core\Extensions\System\AnimalsImport;
use Svr\Data\Models\DataCompaniesLocations;
use Svr\Raw\Models\FromSelexMilk;

class ImportMilk
{
    /**
     * Импорт молочного КСР из RAW в DATA
     *
     * @return bool
     */
    public static function animalsImportMilk(): bool
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

        $animal = FromSelexMilk::where('import_status', ImportStatusEnum::NEW->value)
            ->where('task', 1)
            ->first();

        if (is_null($animal)){
            Log::channel('import_milk')->info('Нет животных по молочному КРС для импорта.');
            return true;
        }

        $animal = $animal->toArray();

        // обновим статус записи животного при импорте на статус - в прогрессе
        FromSelexMilk::where('raw_from_selex_milk_id', $animal['raw_from_selex_milk_id'])
            ->update(['import_status' => ImportStatusEnum::IN_PROGRESS->value,]
            );
        // сопоставим код породы животного из селекса с породой из справочника Хориота
        $result_animal_breed = AnimalsImport::animal_import_breed_check($animal, $animal['npor']);
        $breed_id = (isset($result_animal_breed['breed_id'])) ? $result_animal_breed['breed_id'] : false;
        // сопоставим пол породы животного из селекса с полом из справочника Хориота
        $result_animal_sex = AnimalsImport::animal_import_sex_check($animal['npol']);
        $animal_sex_id = (isset($result_animal_sex['gender_id'])) ? $result_animal_sex['gender_id'] : false;
        // сопоставление инвентарного номера отца животного
        $animal_father_inv = AnimalsImport::animal_import_father_inv_check($animal);
        // сопоставим код породы отца из селекса с породой из справочника Хориота
        $result_father_breed = AnimalsImport::animal_import_breed_check($animal, $animal['npor_otca']);
        $breed_father_id = (isset($result_father_breed['breed_id'])) ? $result_father_breed['breed_id'] : false;

        // сопоставление инвентарного номера матери животного
        $animal_mother_inv = AnimalsImport::animal_import_mother_inv_check($animal);
        // сопоставим код породы отца из селекса с породой из справочника Хориота
        $result_mother_breed = AnimalsImport::animal_import_breed_check($animal, $animal['npor_materi']);
        $breed_mother_id = (isset($result_mother_breed['breed_id'])) ? $result_mother_breed['breed_id'] : false;

        // определение племенной ценности
        $animal_breeding_value = AnimalsImport::animal_import_isp_check($animal);

        // получаем локацию хозяйства
        $company_location_data = DataCompaniesLocations::companyLocationData(false, false, $animal['nobl'], $animal['nrn'], $animal['nhoz']);

        if(!isset($company_location_data['company_location_id']))
        {
            // обновим статус записи животного при импорте на статус - ошибка
            FromSelexMilk::where('raw_from_selex_milk_id', $animal['raw_from_selex_milk_id'])
                ->update(['import_status' => ImportStatusEnum::ERROR->value]
                );
            Log::channel('import_milk')->warning('Ошибка импорта животного по молочному КРС, отсутствует локация хозяйства, GUID_SVR: ' . $animal['guid_svr']);
            return false;
        } else {

            $company_location_id = $company_location_data['company_location_id'];

            // получаем company_id хозяйства рождения животного
            $company_birth_data = DataCompaniesLocations::companyLocationData(
                false, false, false, false, $animal['nhoz_rogd']
            );

            // подставим расчетные поля
            $animal['nhoz'] = $company_location_id;
            $animal['nhoz_keep'] = $company_location_data['company_id'] ?? null;
            $animal['nhoz_rogd'] = $company_birth_data['company_id'] ?? null;
            $animal['npor'] = $breed_id;
            $animal['npol'] = $animal_sex_id;
            $animal['ninv_materi'] = $animal_mother_inv;
            $animal['npor_materi'] = $breed_mother_id;

            $animal['ninv_otca'] = $animal_father_inv;
            $animal['npor_otca'] = $breed_father_id;
            $animal['isp'] = $animal_breeding_value;

            // подготавливаем список полей для животного, которые прописаны в массиве сопоставления $matching_fields
            $result_animal = AnimalsImport::animal_import_value_check($animal, $matching_fields);

            if ($result_animal !== []) {
                // импорт животного в таблицу DATA.DATA_ANIMALS
                $animal_id = AnimalsImport::animal_import_data_animals_add($result_animal);
                if ((int)$animal_id > 0) {
                    // создание всех идентификаторов по животному в таблице DATA.DATA_ANIMALS_CODES
                    // и обновление ключей идентификаторов животного в таблице DATA.DATA_ANIMALS
                    $status = AnimalsImport::animal_import_identifiers_add($animal, $animal_id);
                    if ($status === true) {
                        // обновим статус записи животного при импорте на статус - выполнен
                        FromSelexMilk::where('raw_from_selex_milk_id', $animal['raw_from_selex_milk_id'])
                            ->update(['import_status' => ImportStatusEnum::COMPLETED->value]
                            );
                        Log::channel('import_milk')->info('Импорт животного по молочному КРС успешно завершен, GUID_SVR: ' . $animal['guid_svr']);
                    } else {
                        // обновим статус записи животного при импорте на статус - ошибка
                        FromSelexMilk::where('raw_from_selex_milk_id', $animal['raw_from_selex_milk_id'])
                            ->update(['import_status' => ImportStatusEnum::ERROR->value]
                            );
                        Log::channel('import_milk')->warning('Ошибка создания идентификаторов животного по молочному КРС, GUID_SVR: ' . $animal['guid_svr']);
                    }
                } else {
                    // обновим статус записи животного при импорте на статус - ошибка
                    FromSelexMilk::where('raw_from_selex_milk_id', $animal['raw_from_selex_milk_id'])
                        ->update(['import_status' => ImportStatusEnum::ERROR->value]
                        );
                    Log::channel('import_milk')->warning('Ошибка импорта животного по молочному КРС при добавлении в DATA_ANIMAL, GUID_SVR: ' . $animal['guid_svr']);
                }
            } else {
                Log::channel('import_milk')->warning('Нет полей сопоставления для сохранения данных животного по молочному КРС, GUID_SVR: ' . $animal['guid_svr']);
            }
        }

        return true;
    }
}
