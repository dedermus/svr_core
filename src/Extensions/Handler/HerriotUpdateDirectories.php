<?php

namespace Svr\Core\Extensions\Handler;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Svr\Core\Extensions\Herriot\ApiHerriot;
use Svr\Core\Models\SystemUsersNotifications;
use Svr\Directories\Models\DirectoryAnimalsBreeds;
use Svr\Directories\Models\DirectoryAnimalsSpecies;
use Svr\Directories\Models\DirectoryCountries;
use Svr\Directories\Models\DirectoryKeepingPurposes;
use Svr\Directories\Models\DirectoryKeepingTypes;
use Svr\Directories\Models\DirectoryToolsLocations;

class HerriotUpdateDirectories
{
    //Определение namespaces в SOAP - ответе от API
    private static string $directory_namespace_data            = 'http://api.vetrf.ru/schema/cdm/dictionary/v2';
    private static string $directory_namespace_properties      = 'http://api.vetrf.ru/schema/cdm/base';
    /**
     * Список нужных справочников с атрибутами для работы
     */
    private static array $directories_list								= [
        'keeping_types'									=> [
            'api_method'									=> 'getAnimalKeepingTypeListRequest',
            'get_method'                                    => 'getDirectory',
            'data_container'								=> 'getAnimalKeepingTypeListResponse',
            'data_box'										=> 'animalKeepingTypeList',
            'data_name'										=> 'animalKeepingType',
            'response_data_keys'							=> ['name'],
            'response_props_keys'							=> ['uuid', 'guid'],
            'custom_method'									=> false,
            'method_save'                                   => 'saveKeepingTypes',
        ],
        'animals_species'								=> [
            'api_method'									=> 'getAnimalSpeciesListRequest',
            'get_method'                                    => 'getDirectory',
            'data_container'								=> 'getAnimalSpeciesListResponse',
            'data_box'										=> 'animalSpeciesList',
            'data_name'										=> 'animalSpecies',
            'response_data_keys'							=> ['name', 'code'],
            'response_props_keys'							=> ['uuid', 'guid'],
            'custom_method'									=> false,
            'method_save'                                   => 'saveAnimalsSpecies',
        ],
        'animals_breeds'								=> [
            'api_method'									=> 'getAnimalBreedListRequest',
            'get_method'                                    => 'getDirectory',
            'data_container'								=> 'getAnimalBreedListResponse',
            'data_box'										=> 'animalBreedList',
            'data_name'										=> 'animalBreed',
            'response_data_keys'							=> ['name'],
            'response_props_keys'							=> ['uuid', 'guid'],
            'custom_method'									=> 'animalsBreeds',
            'method_save'                                   => 'saveAnimalsBreeds',
        ],
        'keeping_purposes'								=> [
            'api_method'									=> 'getAnimalKeepingPurposeListRequest',
            'get_method'                                    => 'getDirectory',
            'data_container'								=> 'getAnimalKeepingPurposeListResponse',
            'data_box'										=> 'animalKeepingPurposeList',
            'data_name'										=> 'animalKeepingPurpose',
            'response_data_keys'							=> ['name'],
            'response_props_keys'							=> ['uuid', 'guid'],
            'custom_method'									=> false,
            'method_save'                                   => 'saveKeepingPurposes',
        ],
        'marking_locations'								=> [
            'api_method'									=> 'getAnimalMarkingLocationListRequest',
            'get_method'                                    => 'getDirectory',
            'data_container'								=> 'getAnimalMarkingLocationListResponse',
            'data_box'										=> 'animalMarkingLocationList',
            'data_name'										=> 'animalMarkingLocation',
            'response_data_keys'							=> ['name'],
            'response_props_keys'							=> ['uuid', 'guid'],
            'custom_method'									=> false,
            'method_save'                                   => 'saveMarkingLocations',
        ],
        'countries'								        => [
            'api_method'									=> 'getAllCountryListRequest',
            'get_method'                                    => 'getDirectoryCountries',
            'data_container'								=> 'getAllCountryListResponse',
            'data_box'										=> 'countryList',
            'data_name'										=> 'country',
            'response_data_keys'							=> ['name', 'fullName', 'englishName', 'code', 'code3'],
            'response_props_keys'							=> ['uuid', 'guid'],
            'custom_method'									=> false,
            'method_save'                                   => 'saveCountries',
        ]
    ];

    /**
     * Идем в Хорриот получать справочники
     * @return bool
     */
    public static function getDirectories(): bool
    {
        $herriot_user = env('HERRIOT_USER', false);
        $herriot_password = env('HERRIOT_PASSWORD', false);

        if (!$herriot_user || !$herriot_password)
        {
            Log::channel('herriot_directories')->warning('Обновление справочников. Не заданы логин или пароль от хорриота.');
            (new SystemUsersNotifications)->notificationsSendAdmin('Обновление справочников из Хорриот. Не заданы логин или пароль от хорриота. (HerriotUpdateDirectories.php)');
            return false;
        }

        //Экземпляр класса работы с API Хорриот
        $api = new ApiHerriot($herriot_user, $herriot_password);

        /**
         * Основной цикл последовательных запросов справочников и их обработки
         */
        foreach(self::$directories_list as $request_data)
        {
            $result = $api->{$request_data['get_method']}($request_data['api_method']);

            if($result === false || (is_array($result) && isset($result['error'])))
            {
                Log::channel('herriot_directories')->warning('Обновление справочников из Хорриот. '.$request_data['api_method'].'. Не пришли данные из Хорриот.', [$result]);
                (new SystemUsersNotifications)->notificationsSendAdmin('Обновление справочников из Хорриот. '.$request_data['api_method'].'. Не пришли данные из Хорриот. (HerriotUpdateDirectories.php)');
                continue;
            }

            $directory_xml = simplexml_load_string($result);

            if ($directory_xml === false)
            {
                Log::channel('herriot_directories')->warning('Обновление справочников из Хорриот. '.$request_data['api_method'].'. Не валидный XML из Хорриота.', [$result]);
                (new SystemUsersNotifications)->notificationsSendAdmin('Обновление справочников из Хорриот. '.$request_data['api_method'].'. Не валидный XML из Хорриота. (HerriotUpdateDirectories.php)');
                continue;
            }

            $directory_path	= $directory_xml->children('http://schemas.xmlsoap.org/soap/envelope/');

            if (!isset($directory_path->Body))
            {
                Log::channel('herriot_directories')->warning('Обновление справочников из Хорриот. '.$request_data['api_method'].'. Нет BODY из Хорриота.)', [$result]);
                (new SystemUsersNotifications)->notificationsSendAdmin('Обновление справочников из Хорриот. '.$request_data['api_method'].'. Нет BODY из Хорриота. (HerriotUpdateDirectories.php)');
                continue;
            }

            $body = $directory_path->Body;
            $v2	= $body->children('http://api.vetrf.ru/schema/cdm/registry/ws-definitions/v2');

            if (!isset($v2->{$request_data['data_container']}))
            {
                Log::channel('herriot_directories')->warning('Обновление справочников из Хорриот. '.$request_data['api_method'].'. Не пришел data_container из Хорриота.', [$result]);
                (new SystemUsersNotifications)->notificationsSendAdmin('Обновление справочников из Хорриот. '.$request_data['api_method'].'. Не пришел data_container из Хорриота. (HerriotUpdateDirectories.php)');
                continue;
            }

            $getAllCountryListResponse					= $v2->{$request_data['data_container']};
            $dt											= $getAllCountryListResponse->children('http://api.vetrf.ru/schema/cdm/dictionary/v2');

            if (!isset($dt->{$request_data['data_box']}))
            {
                Log::channel('herriot_directories')->warning('Обновление справочников из Хорриот. '.$request_data['api_method'].'. Не пришел data_box из Хорриота.', [$result]);
                (new SystemUsersNotifications)->notificationsSendAdmin('Обновление справочников из Хорриот. '.$request_data['api_method'].'. Не пришел data_box из Хорриота. (HerriotUpdateDirectories.php)');
                continue;
            }

            $country_list								= $dt->{$request_data['data_box']};
            $countryList								= $country_list->children("http://api.vetrf.ru/schema/cdm/dictionary/v2");

            $directory_result							= [];

            foreach ($countryList as $item)
            {
                $a										= $item->children("http://api.vetrf.ru/schema/cdm/base");

                foreach($item as $key => $value)
                {
                    $item_result[$key]					= (string)$value;
                }

                foreach($a as $key => $value)
                {
                    $item_result[$key]					= (string)$value;
                }

                if($request_data['custom_method'])
                {
                    $custom_result						= self::{$request_data['custom_method']}($item, $a);
                    $directory_result[]					= array_merge($item_result, $custom_result);
                }else{
                    $directory_result[]					= $item_result;
                }

            }

            self::{$request_data['method_save']}($directory_result);
        }
        return true;
    }

    /**
     * Метод извлечения вложенных данных категории для справочника пород
     */
    static function animalsBreeds($item_data, $item_properties)
    {
        $species									= $item_data->species;
        $species_properties							= $species->children(self::$directory_namespace_properties);
        $species_data								= $species->children(self::$directory_namespace_data);

        $animals_breeds_result							= [
            'category_code'							=> (string)$species_data->code,
            'category_name'							=> (string)$species_data->name,
            'category_guid'							=> (string)$species_properties->guid,
        ];

        return $animals_breeds_result;
    }


    /**
     * Метод сохранения данных для типов содержания животных
     */
    static function saveKeepingTypes($directory_items_list)
    {
        foreach ($directory_items_list as $key => $value) {

            $item = DirectoryKeepingTypes::where('keeping_type_guid_horriot', '=', $value['guid'])->first();

            if (empty($item))
            {
                $directory_item = [
                    'keeping_type_guid_self' => Str::uuid(),
                    'keeping_type_guid_horriot' => $value['guid'],
                    'keeping_type_name' => $value['name'],
                    'created_at' => date('Y-m-d H:i:s')
                ];
                DB::table(DirectoryKeepingTypes::getTableName())->insert($directory_item);
            }
            else
            {
                if ($item['keeping_type_uuid_horriot'] != $value['uuid'])
                {
                    $directory_item = [
                        'keeping_type_uuid_horriot' => $value['uuid'],
                        'keeping_type_name' => $value['name'],
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    DB::table(DirectoryKeepingTypes::getTableName())->where('keeping_type_id', '=', $item['keeping_type_id'])
                        ->update($directory_item);
                }
            }
        }
    }


    /**
     * Метод сохранения данных для видов животных
     */
    static function saveAnimalsSpecies($directory_items_list)
    {
        foreach ($directory_items_list as $key => $value) {

            $item = DirectoryAnimalsSpecies::where('specie_guid_horriot', '=', $value['guid'])->first();

            if (empty($item))
            {
                $directory_item = [
                    'specie_guid_self'      => Str::uuid()->toString(),
                    'specie_guid_horriot'   => $value['guid'],
                    'specie_name'           => $value['name'],
                    'created_at'            => date('Y-m-d H:i:s')
                ];
                DB::table(DirectoryAnimalsSpecies::getTableName())->insert($directory_item);
            }
            else
            {
                if ($item['specie_uuid_horriot'] != $value['uuid'])
                {
                    $directory_item = [
                        'specie_uuid_horriot' => $value['uuid'],
                        'specie_name' => $value['name'],
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    DB::table(DirectoryAnimalsSpecies::getTableName())->where('specie_id', '=', $item['specie_id'])
                        ->update($directory_item);
                }
            }
        }
    }


    /**
     * Метод сохранения данных для пород животных
     */
    static function saveAnimalsBreeds($directory_items_list)
    {
        foreach ($directory_items_list as $key => $value)
        {
            $item = DirectoryAnimalsBreeds::where('breed_guid_horriot', '=', $value['guid'])->first();

            $specie_id = DirectoryAnimalsSpecies::where('specie_guid_horriot', '=', $value['category_guid'])->first()->toArray();

            if (empty($item))
            {
                if (!empty($specie_id))
                {
                    $directory_item = [
                        'specie_id'             => $specie_id['specie_id'],
                        'breed_guid_self'       => Str::uuid(),
                        'breed_guid_horriot'    => $value['guid'],
                        'breed_uuid_horriot'    => $value['uuid'],
                        'breed_name'            => $value['name'],
                        'created_at'            => date('Y-m-d H:i:s')
                    ];

                    DB::table(DirectoryAnimalsBreeds::getTableName())->insert($directory_item);
                }
            }
            else
            {
                if ($item['breed_uuid_horriot'] != $value['uuid'])
                {
                    if ($specie_id !== false)
                    {
                        $directory_item = [
                            'specie_id' => $specie_id['specie_id'],
                            'breed_uuid_horriot' => $value['uuid'],
                            'breed_name' => $value['name'],
                            'updated_at' => date('Y-m-d H:i:s')
                        ];

                        DB::table(DirectoryAnimalsBreeds::getTableName())->where('breed_guid_horriot', '=', $item['breed_guid_horriot'])
                            ->update($directory_item);
                    }
                }
            }
        }
    }


    /**
     * Метод сохранения данных для целей содержания животных
     */
    static function saveKeepingPurposes($directory_items_list)
    {
        foreach ($directory_items_list as $key => $value)
        {
            $item = DirectoryKeepingPurposes::where('keeping_purpose_guid_horriot', '=', $value['guid'])->first();

            if (empty($item))
            {
                $directory_item = [
                    'keeping_purpose_guid_self'       => Str::uuid(),
                    'keeping_purpose_guid_horriot'    => $value['guid'],
                    'keeping_purpose_name'            => $value['name'],
                    'created_at'                      => date('Y-m-d H:i:s')
                ];

                DB::table(DirectoryKeepingPurposes::getTableName())->insert($directory_item);
            }
            else
            {
                if ($item['keeping_purpose_uuid_horriot'] != $value['uuid'])
                {
                    $directory_item = [
                        'keeping_purpose_uuid_horriot' => $value['uuid'],
                        'keeping_purpose_name' => $value['name'],
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    DB::table(DirectoryKeepingPurposes::getTableName())->where('keeping_purpose_id', '=', $item['keeping_purpose_id'])
                        ->update($directory_item);
                }
            }
        }
    }

    /**
     * Метод сохранения данных для мест нанесения маркировки животных
     */
    static function saveMarkingLocations($directory_items_list)
    {
        foreach ($directory_items_list as $key => $value) {

            $item = DirectoryToolsLocations::where('tool_location_guid_horriot', '=', $value['guid'])->first();

            if (empty($item))
            {
                $directory_item = [
                    'tool_location_guid_self'       => Str::uuid(),
                    'tool_location_guid_horriot'    => $value['guid'],
                    'tool_location_name'            => $value['name'],
                    'created_at'                    => date('Y-m-d H:i:s')
                ];

                DB::table(DirectoryToolsLocations::getTableName())->insert($directory_item);
            }
            else
            {
                if ($item['tool_location_uuid_horriot'] != $value['uuid'])
                {
                    $directory_item = [
                        'tool_location_uuid_horriot' => $value['uuid'],
                        'tool_location_name' => $value['name'],
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    DB::table(DirectoryKeepingPurposes::getTableName())->where('tool_location_id', '=', $item['tool_location_id'])
                        ->update($directory_item);
                }
            }
        }
    }

    /**
     * Метод сохранения данных стран
     */
    static function saveCountries($directory_items_list)
    {
        foreach ($directory_items_list as $key => $value) {

            $item = DirectoryCountries::where('country_guid_horriot', '=', $value['guid'])->first();

            if (empty($item))
            {
                $directory_item = [
                    'country_guid_self'     => Str::uuid(),
                    'country_guid_horriot'  => $value['guid'],
                    'country_uuid_horriot'  => $value['uuid'],
                    'country_name'          => $value['name'],
                    //'country_fullName'    => $value['fullName'],
                    'country_name_eng'      => $value['englishName'],
                    'country_kod'           => $value['code'],
                    'country_kod3'          => $value['code3'],
                    'created_at'            => date('Y-m-d H:i:s')
                ];

                DB::table(DirectoryCountries::getTableName())->insert($directory_item);
            }
            else
            {
                if ($item['country_uuid_horriot'] != $value['uuid'])
                {
                    $directory_item = [
                        'country_uuid_horriot' => $value['uuid'],
                        'country_name' => $value['name'],
                        //'country_fullName' => $value['fullName'],
                        'country_name_eng' => $value['englishName'],
                        'country_kod' => $value['code'],
                        'country_kod3' => $value['code3'],
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    DB::table(DirectoryCountries::getTableName())->where('country_id', '=', $item['country_id'])
                        ->update($directory_item);
                }
            }
        }
    }
}
