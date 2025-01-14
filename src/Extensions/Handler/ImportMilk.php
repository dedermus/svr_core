<?php

namespace Svr\Core\Extensions\Handler;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Svr\Core\Enums\ImportStatusEnum;
use Svr\Core\Enums\SystemSexEnum;
use Svr\Core\Enums\SystemStatusDeleteEnum;
use Svr\Data\Models\DataAnimals;
use Svr\Data\Models\DataAnimalsCodes;
use Svr\Data\Models\DataCompaniesLocations;
use Svr\Data\Models\DataCompaniesObjects;
use Svr\Directories\Models\DirectoryAnimalsBreeds;
use Svr\Directories\Models\DirectoryAnimalsSpecies;
use Svr\Directories\Models\DirectoryGenders;
use Svr\Directories\Models\DirectoryMarkStatuses;
use Svr\Directories\Models\DirectoryMarkToolTypes;
use Svr\Directories\Models\DirectoryMarkTypes;
use Svr\Directories\Models\DirectoryToolsLocations;
use Svr\Raw\Models\FromSelexMilk;

class ImportMilk
{
    public static function animalsImport(): void
    {

        Log::channel('import')->info('Импорт молочных животных из RAW в DATA.');
        self::animalsImportMilk();
    }

    /**
     * Запрос на внешний ресурс для получения списка хозяйств
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

        Log::channel('import')->info('Импорт молочных коров КРС');

        $animal = FromSelexMilk::where(['import_status' => ImportStatusEnum::NEW->value, 'task' => 1])->first();
        Log::channel('import')->info('Импорт молочных коров КРС '. $animal);

        if ($animal === null)
        {
            Log::channel('import')->info('- импорт молочных коров завершен');
            return true;
        } else {
            $animal = $animal->toArray();
        }

        // обновим статус записи животного при импорте на статус - в прогрессе
        FromSelexMilk::where('raw_from_selex_milk_id', $animal['raw_from_selex_milk_id'])
            ->update(['import_status' => ImportStatusEnum::IN_PROGRESS->value,]
        );
        Log::channel('import')->info('- животное: ' . $animal['guid_svr']);
        Log::channel('import')->info('  попытка записи в общую таблицу, guid: ' . $animal['guid_svr']);
        // сопоставим код породы животного из селекса с породой из справочника Хориота
        $result_animal_breed = self::animal_import_breed_check($animal, $animal['npor']);
        $breed_id = (isset($result_animal_breed['breed_id'])) ? $result_animal_breed['breed_id'] : false;
        // сопоставим пол породы животного из селекса с полом из справочника Хориота
        $result_animal_sex = self::animal_import_sex_check($animal['npol']);
        $animal_sex_id = (isset($result_animal_sex['gender_id'])) ? $result_animal_sex['gender_id'] : false;
        // сопоставление инвентарного номера отца животного
        $animal_father_inv = self::animal_import_father_inv_check($animal);
        // сопоставим код породы отца из селекса с породой из справочника Хориота
        $result_father_breed = self::animal_import_breed_check($animal, $animal['npor_otca']);
        $breed_father_id = (isset($result_father_breed['breed_id'])) ? $result_father_breed['breed_id'] : false;

        // сопоставление инвентарного номера матери животного
        $animal_mother_inv = self::animal_import_mother_inv_check($animal);
        // сопоставим код породы отца из селекса с породой из справочника Хориота
        $result_mother_breed = self::animal_import_breed_check($animal, $animal['npor_materi']);
        $breed_mother_id = (isset($result_mother_breed['breed_id'])) ? $result_mother_breed['breed_id'] : false;

        // определение племенной ценности
        $animal_breeding_value = self::animal_import_isp_check($animal);

        // получаем локацию хозяйства
        $company_location_data = DataCompaniesLocations::companyLocationData(false, false, $animal['nobl'], $animal['nrn'], $animal['nhoz']);

        if(!isset($company_location_data['company_location_id']))
        {
            // обновим статус записи животного при импорте на статус - ошибка
            FromSelexMilk::where('raw_from_selex_milk_id', $animal['raw_from_selex_milk_id'])
                ->update(['import_status' => ImportStatusEnum::ERROR->value]
                );
            Log::channel('import')->warning('  ошибка импорта животного');
        } else {

            $company_location_id = $company_location_data['company_location_id'];

            // получаем company_id хозяйства рождения животного
            $company_birth_data = DataCompaniesLocations::companyLocationData(
                false, false, false, false, $animal['nhoz_rogd']
            );

            // подставим расчетные поля
            $animal['nhoz'] = $company_location_id;
            $animal['nhoz_keep'] = $company_location_data !== false ? $company_location_data['company_id'] : null;
            $animal['nhoz_rogd'] = $company_birth_data !== false ? $company_birth_data['company_id'] : null;
            $animal['npor'] = $breed_id;
            $animal['npol'] = $animal_sex_id;
            $animal['ninv_materi'] = $animal_mother_inv;
            $animal['npor_materi'] = $breed_mother_id;

            $animal['ninv_otca'] = $animal_father_inv;
            $animal['npor_otca'] = $breed_father_id;
            $animal['isp'] = $animal_breeding_value;

            // подготавливаем список полей для животного, которые прописаны в массиве сопоставления $matching_fields
            $result_animal = self::animal_import_value_check($animal, $matching_fields);

            if ($result_animal !== []) {
                // импорт животного в таблицу DATA.DATA_ANIMALS
                $animal_id = self::animal_import_data_animals_add($result_animal);
                if ((int)$animal_id > 0) {
                    // создание всех идентификаторов по животному в таблице DATA.DATA_ANIMALS_CODES
                    // и обновление ключей идентификаторов животного в таблице DATA.DATA_ANIMALS
                    $status = self::animal_import_identifiers_add($animal, $animal_id);
                    if ($status === true) {
                        // обновим статус записи животного при импорте на статус - выполнен
                        FromSelexMilk::where('raw_from_selex_milk_id', $animal['raw_from_selex_milk_id'])
                            ->update(['import_status' => ImportStatusEnum::COMPLETED->value]
                            );
                        Log::channel('import')->info('  импорт животного завершен');
                    } else {
                        // обновим статус записи животного при импорте на статус - ошибка
                        FromSelexMilk::where('raw_from_selex_milk_id', $animal['raw_from_selex_milk_id'])
                            ->update(['import_status' => ImportStatusEnum::ERROR->value]
                            );
                        Log::channel('import')->warning('  ошибка импорта животного');
                    }
                } else {
                    // обновим статус записи животного при импорте на статус - ошибка
                    FromSelexMilk::where('raw_from_selex_milk_id', $animal['raw_from_selex_milk_id'])
                        ->update(['import_status' => ImportStatusEnum::ERROR->value]
                        );
                    Log::channel('import')->warning('  ошибка импорта животного');
                }
            } else {
                Log::channel('import')->warning('  нет полей сопоставления для сохранения данных по животном');
            }
        }

        return true;
    }

    /**
     * Сопоставление кода породы из селекса с породой из справочника Хорриот
     *
     * @param array $animal         - массив атрибутов животного
     * @param int   $breed_code     - код породы, передается код породы животного или мамы, или отца
     *
     * @return false|array
     */
    private static function animal_import_breed_check(array $animal, int $breed_code): false|array
    {
        if (empty($breed_code)) return false;

        $breed = DirectoryAnimalsBreeds::leftJoin(DirectoryAnimalsSpecies::getTableName(), function ($join) {
            $join->on(DirectoryAnimalsSpecies::getTableName().'.specie_id', '=', DirectoryAnimalsBreeds::getTableName().'.specie_id');
        }) ->where(DirectoryAnimalsSpecies::getTableName().'.specie_id', $animal['animal_vid_cod'])
            ->where(DirectoryAnimalsBreeds::getTableName().'.breed_selex_code', $breed_code)
            ->first();
        if ($breed === null) {
            return false;
        }

        return $breed->toArray();
    }

    /**
     * Сопоставление кода пола из селекса с полом из справочника Хорриот
     *
     * @param int $sex  - код пола, передается код пола животного или мамы, или отца
     *
     * @return false|array
     */
    private static function animal_import_sex_check(int $sex): false|array
    {
        $gender = DirectoryGenders::where('gender_selex_code', $sex)->first();

        if ($gender === null) {
            return false;
        }

        return $gender->toArray();
    }

    /**
     * Сопоставление инвентарного номера отца животного
     *
     * @param array $animal      - массив аттрибутов животного
     *
     * @return mixed|null
     */
    private static function animal_import_father_inv_check(array $animal): mixed
    {
        $inv = null;    // инвентарный номер по умолчанию
        // если есть код вида животного
        if (isset($animal['animal_vid_cod'])) {
            // перейдем по условию кода вида животного
            switch ($animal['animal_vid_cod']) {
                // МРС (овцы)
                case 17:
                    // отец - инвентарный номер, правое ухо
                    $ninvright = (isset($animal['ninvright_otca'])) ? $animal['ninvright_otca'] : false;
                    // отец - инвентарный номер, левое ухо
                    $ninvleft = (isset($animal['ninvleft_otca'])) ? $animal['ninvleft_otca'] : false;
                    // если правое ухо передано
                    $inv = ($ninvright !== false && !is_null($ninvright)) ? $ninvright : $inv;
                    // если правое ухо не передано, но передано левое ухо
                    $inv = (is_null($inv) && $ninvleft !== false && !is_null($ninvleft)) ? $ninvleft : $inv;
                    break;
                // КРС (молоко, мясо)
                case 26:
                    $inv = (isset($animal['ninv_otca'])) ? $animal['ninv_otca'] : $inv;
                    break;
            }
        }
        return $inv;
    }

    /**
     * Сопоставление инвентарного номера матери животного
     *
     * @param array $animal           - массив аттрибутов животного
     *
     * @return mixed|null
     */
    private static function animal_import_mother_inv_check(array $animal): mixed
    {
        $inv = null;    // инвентарный номер по умолчанию
        // если есть код вида животного
        if (isset($animal['animal_vid_cod'])) {
            // перейдем по условию кода вида животного
            switch ($animal['animal_vid_cod']) {
                // МРС (овцы)
                case 17:
                    // мать - инвентарный номер, правое ухо
                    $ninvright = (isset($animal['ninvright_materi'])) ? $animal['ninvright_materi'] : false;
                    // мать - инвентарный номер, левое ухо
                    $ninvleft = (isset($animal['ninvleft_materi'])) ? $animal['ninvleft_materi'] : false;
                    // если правое ухо передано
                    $inv = ($ninvright !== false && !is_null($ninvright)) ? $ninvright : $inv;
                    // если правое ухо не передано, но передано левое ухо
                    $inv = (is_null($inv) && $ninvleft !== false && !is_null($ninvleft)) ? $ninvleft : $inv;
                    break;
                // КРС (молоко, мясо)
                case 26:
                    $inv = (isset($animal['ninv_materi'])) ? $animal['ninv_materi'] : $inv;
                    break;
            }
        }
        return $inv;
    }

    /**
     * Определение племенной ценности
     * 'UNDEFINED' - не определено, 'BREEDING' - Целевое, 'NON_BREEDING' - Пользовательное
     *
     * @param array $animal   - объект животного
     *
     * @return string
     */
    private static function animal_import_isp_check(array $animal): string
    {
        // Значение «Использование» из СЕЛЭКС:
        // •	Целевое
        //          Значение справочника «Племенная ценность» - «Племенное»
        //          Значения «Использование» из СЕЛЭКС:
        // •	Пользовательное
        //          Значение справочника «Племенная ценность» - «Не племенное»
        // Иначе:
        //      Значение справочника «Племенная ценность» - «Тип не определен»
        $animal['isp'] = (isset($animal['isp'])) ? $animal['isp'] : 'Тип не определен';
        $isp = 'UNDEFINED';
        switch ($animal['isp']) {
            case 'Пользовательное': $isp = 'NON_BREEDING';
                break;
            case 'Целевое': $isp = 'BREEDING';
                break;
        }
        return $isp;
    }

    /**
     * Проверка и сопоставление значений животного
     *
     * @param array $animal         - массив данных животного
     * @param array $matching_fields - массив сопоставления полей
     *
     * @return array
     */
    private static function animal_import_value_check(array $animal, array $matching_fields): array
    {
        $result_animal = []; // результирующий массив по животному

        // Пройдемся по массиву сопоставления полей
        foreach ($matching_fields as $key => $field) {
            // Если поле сопоставления присутствует у животного
            if (isset($animal[$key]) && !empty($animal[$key])) {
                $result_animal[$field] = $animal[$key];
            }
        }

        if (!empty($result_animal)) {
            $result_animal['animal_date_create_record'] = date('Y-m-d');

            if (isset($result_animal['animal_place_of_keeping_id'])) {
                $company_objects_list = DataCompaniesObjects::companyObjectsList($result_animal['animal_place_of_keeping_id']);
                if (!empty($company_objects_list)) {
                    $result_animal['animal_object_of_keeping_id'] = $company_objects_list[0]['company_object_id'];
                }
            }

            if (isset($result_animal['animal_place_of_birth_id'])) {
                $company_objects_list = DataCompaniesObjects::companyObjectsList($result_animal['animal_place_of_birth_id']);
                if (!empty($company_objects_list)) {
                    $result_animal['animal_object_of_birth_id'] = $company_objects_list[0]['company_object_id'];
                }
            }
        }

        return $result_animal;
    }

    /**
     * Импорт животного в таблицу DATA.DATA_ANIMALS
     * @param $animal           - сборный массив по животному
     *
     * @return mixed
     */
    private static function animal_import_data_animals_add($animal): mixed
    {
        if (!isset($animal['animal_type_of_keeping_id']))
        {
            $animal['animal_type_of_keeping_id'] = 1;
        }
        if (!isset($animal['animal_purpose_of_keeping_id']))
        {
            $animal['animal_purpose_of_keeping_id'] = 15;
        }

        switch ($animal['animal_sex_id'])
        {
            case 1:
            case 3:
                $animal['animal_sex'] = SystemSexEnum::MALE->value;
                break;
            case 2:
            case 4:
                $animal['animal_sex'] = SystemSexEnum::FEMALE->value;
                break;
        }
        $request = Request::create(
            uri: '/v1',
            method: 'post'
        );
        $request->replace(
            $animal
        );

        return (new DataAnimals)->animalCreate($request);
    }

    /**
     * Создание всех идентификаторов по животному в таблице DATA.DATA_ANIMALS_CODES
     * и обновление ключей идентификаторов животного в таблице DATA.DATA_ANIMALS
     *
     * @param $animal       - объект животного
     * @param $animal_id    - ANIMAL_ID нового животного из таблицы DATA.DATA_ANIMALS
     *
     * @return bool
     */
    private static function animal_import_identifiers_add($animal, $animal_id): bool
    {
        // таблица data.data_animals_codes
        // поля для записи идентификатор животного, сопоставление (левая часть - поля для импорта / правая часть - поля назначения) для таблицы
        $codes_fields = [
            'ngosregister'          => [
                'mark_type_value_horriot'       =>  'rshn',                 // значение для хорриота видов номеров
                'mark_status_value_horriot'     =>  'main',                  // значение для хорриота статуса номера (code_status_id)
                'mark_tool_type_value_horriot'  =>  'label',                 // значение для хорриота типа маркирования (code_tool_type_id)
                'tool_location_guid_horriot'  	=>  'b4605a56-4845-a2f1-d43d-e305a36bff39', // значение для хорриота места нанесения маркирования (code_tool_location_id)
                'matching_field'                =>  'animal_code_rshn_id',  // животное - идентификационный номер РСХН
                'field_code_tool_date_set'		=>	'date_ngosregister'		// поле, из которого брать дату чипирования
            ],
            'ninv'                  => [
                'mark_type_value_horriot'       =>  'inv',                   // значение для хорриота видов номеров (code_type_id)
                'mark_status_value_horriot'     =>  'additional',            // значение для хорриота статуса номера (code_status_id)
                'mark_tool_type_value_horriot'  =>  'label',                 // значение для хорриота типа маркирования (code_tool_type_id)
                'tool_location_guid_horriot'  	=>  'b4605a56-4845-a2f1-d43d-e305a36bff39', // значение для хорриота места нанесения маркирования (code_tool_location_id)
                'matching_field'                =>  'animal_code_inv_id',     // животное - инвентарный номер
                'field_code_tool_date_set'		=>	'date_ninv'				 // поле, из которого брать дату чипирования
            ],
            'ninvright'             => [
                'mark_type_value_horriot'       =>  'right',                // значение для хорриота видов номеров
                'mark_status_value_horriot'     =>  'additional',           // значение для хорриота статуса номера (code_status_id)
                'mark_tool_type_value_horriot'  =>  'label',                // значение для хорриота типа маркирования (code_tool_type_id)
                'tool_location_guid_horriot'  	=>  'b4605a56-4845-a2f1-d43d-e305a36bff39', // значение для хорриота места нанесения маркирования (code_tool_location_id)
                'matching_field'                =>  'animal_code_right_id', // животное - инвентарный номер, правое ухо
                'field_code_tool_date_set'		=>	'date_ninvright'		// поле, из которого брать дату чипирования
            ],
            'ninvleft'              => [
                'mark_type_value_horriot'       =>  'left',                 // значение для хорриота видов номеров
                'mark_status_value_horriot'     =>  'additional',           // значение для хорриота статуса номера (code_status_id)
                'mark_tool_type_value_horriot'  =>  'label',                // значение для хорриота типа маркирования (code_tool_type_id)
                'tool_location_guid_horriot'  	=>  'ca878fa9-a995-019e-22b9-7bb0e4b5aa90', // значение для хорриота места нанесения маркирования (code_tool_location_id)
                'matching_field'                =>  'animal_code_left_id',  // животное - инвентарный номер, левое ухо
                'field_code_tool_date_set'		=>	'date_ninvleft'			// поле, из которого брать дату чипирования
            ],
            'ninv1'                 =>[
                'mark_type_value_horriot'       =>  'device',               // значение для хорриота видов номеров
                'mark_status_value_horriot'     =>  'additional',           // значение для хорриота статуса номера (code_status_id)
                'mark_tool_type_value_horriot'  =>  'collar',               // значение для хорриота типа маркирования (code_tool_type_id)
                'tool_location_guid_horriot'  	=>  '598f6149-039a-9598-2680-219daf09c1ea', // значение для хорриота места нанесения маркирования (code_tool_location_id)
                'matching_field'                =>  'animal_code_device_id' // животное - номер в оборудовании
            ],
            'ninv3'                 => [
                'mark_type_value_horriot'       =>  'chip',                 // значение для хорриота видов номеров
                'mark_status_value_horriot'     =>  'additional',           // значение для хорриота статуса номера (code_status_id)
                'mark_tool_type_value_horriot'  =>  'electronic_tag',       // значение для хорриота типа маркирования (code_tool_type_id)
                'tool_location_guid_horriot'  	=>  'b4605a56-4845-a2f1-d43d-e305a36bff39', // значение для хорриота места нанесения маркирования (code_tool_location_id)
                'matching_field'                =>  'animal_code_chip_id',  // животное - электронная метка
                'field_code_tool_date_set'		=>	'date_chip'				// поле, из которого брать дату чипирования
            ],
            'taty'                  => [
                'mark_type_value_horriot'       =>  'tattoo',               // значение для хорриота видов номеров
                'mark_status_value_horriot'     =>  'additional',           // значение для хорриота статуса номера (code_status_id)
                'mark_tool_type_value_horriot'  =>  'tattoo',               // значение для хорриота типа маркирования (code_tool_type_id)
                'tool_location_guid_horriot'  	=>  'b4605a56-4845-a2f1-d43d-e305a36bff39', // значение для хорриота места нанесения маркирования (code_tool_location_id)
                'matching_field'                =>  'animal_code_tattoo_id',// животное - тату
            ],
            'nident'                => [
                'mark_type_value_horriot'       =>  'import',                // значение для хорриота видов номеров
                'mark_status_value_horriot'     =>  'additional',            // значение для хорриота статуса номера (code_status_id)
                'mark_tool_type_value_horriot'  =>  'label',                 // значение для хорриота типа маркирования (code_tool_type_id)
                'tool_location_guid_horriot'  	=>  'b4605a56-4845-a2f1-d43d-e305a36bff39', // значение для хорриота места нанесения маркирования (code_tool_location_id)
                'matching_field'                =>  'animal_code_import_id', // животное - импортный идентификатор
            ],
            'klichka'               => [
                'mark_type_value_horriot'       =>  'name',                  // значение для хорриота видов номеров
                'mark_status_value_horriot'     =>  'false',            	 // значение для хорриота статуса номера (code_status_id)
                'mark_tool_type_value_horriot'  =>  'false',                 // значение для хорриота типа маркирования (code_tool_type_id)
                'tool_location_guid_horriot'  	=>  'false', 				 // значение для хорриота места нанесения маркирования (code_tool_location_id)
                'matching_field'                =>  'animal_code_name_id',   // животное - кличка,
            ],
        ];

        $status = false;    // флаг успешного создания идентификатора животного

        // переберем список идентификаторов
        foreach ($codes_fields as $key => $field)
        {
            // если идентификатор есть у животного
            if (isset($animal[$key]))
            {
                // если идентификатор не равен NULL
                if (!is_null($animal[$key]) && !empty($animal[$key]))
                {
                    // Пытаемся достать тип идентификатора
                    $code_type = DirectoryMarkTypes::where('mark_type_value_horriot', $field['mark_type_value_horriot'])->first();
                    // Используем null-safe оператор для получения mark_type_id или null, если объект не найден
                    $code_type_id = $code_type?->mark_type_id;

                    // Пытаемся достать статус номера
                    $code_status = DirectoryMarkStatuses::where('mark_status_value_horriot', $field['mark_status_value_horriot'])->first();
                    // Используем null-safe оператор для получения mark_status_id или null, если объект не найден
                    $code_status_id = $code_status?->mark_status_id;

                    // Пытаемся достать тип маркирования
                    $code_tool_type = DirectoryMarkToolTypes::where('mark_tool_type_value_horriot', $field['mark_tool_type_value_horriot'])->first();
                    // Используем null-safe оператор для получения mark_tool_type_id или null, если объект не найден
                    $code_tool_type_id = $code_tool_type?->mark_tool_type_id;

                    // пытаемся достать место нанесения маркирования
                    $code_tool_location = DirectoryToolsLocations::where('tool_location_guid_horriot', $field['tool_location_guid_horriot'])->first();
                    // Используем null-safe оператор для получения tool_location_id или null, если объект не найден
                    $code_tool_location_id = $code_tool_location?->tool_location_id;

                    $code_tool_date_set = null;
                    if (isset($field['field_code_tool_date_set']))
                    {
                        if (isset($animal[$field['field_code_tool_date_set']]))
                        {
                            $code_tool_date_set = strtotime($animal[$field['field_code_tool_date_set']]) ? date('Y-m-d', strtotime($animal[$field['field_code_tool_date_set']])) : null;
                        }
                    }

                    // если тип идентификатора есть, то пишем его
                    if ($code_type_id) {
                        // массив для вставки
                        $data = [
                            'animal_id'    			=> $animal_id,      // ID животного
                            'code_type_id' 			=> $code_type_id,   // тип идентификатора
                            'code_value'   			=> $animal[$key],   // значение
                            'code_description'  	=> (isset($code_type['mark_type_name'])) ? $code_type['mark_type_name'] : null, // описание типа идентификатора
                            'code_status_id'		=> $code_status_id,
                            'code_tool_type_id'		=> $code_tool_type_id,
                            'code_tool_location_id' => $code_tool_location_id,
                            'code_tool_date_set'	=> (!is_null($code_tool_date_set)) ? date('Y-m-d H:i:s', strtotime($code_tool_date_set)) : $code_tool_date_set,
                            'code_status_delete'	=> SystemStatusDeleteEnum::ACTIVE->value,
                        ];

                        $request = Request::create(
                            uri: '/v1',
                            method: 'post'
                        );
                        $request->replace(
                            $data
                        );

                        // создаем идентификатор в таблице DATA.DATA_ANIMALS_CODES
                        $code_id = (new DataAnimalsCodes)->animalCodeCreate($request);

                        // если идентификатор успешно создан
                        if ($code_id) {
                            // обновляем ссылочный ключ на идентификатор
                            DataAnimals::where('animal_id', $animal_id)
                                ->update([
                                    $field['matching_field'] => $code_id
                                ]);
                            $status = true;
                        }
                    }
                }
            }
        }
        return $status;
    }
}
