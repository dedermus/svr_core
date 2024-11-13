<?php

namespace Svr\Core\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class SystemRolesRightSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('system.system_roles_rights')->truncate();

        DB::table('system.system_roles_rights')->insert([
            /* === Вет. врач хоз-ва === */
            [
                "role_right_id"         => "1",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "auth_login",        					// Авторизация
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "2",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "auth_logout",       					// Выход
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "3",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "auth_info", 							// Информация о себе
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "4",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "auth_set",  							// Установка хозяйства
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "5",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "auth_herriot_requisites",   			// Редактирование реквизитов хорриота
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "7",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "companies_data",    					// Данные хозяйства
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "8",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "companies_list",    					// Список хозяйств
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "9",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "companies_locations_list",  			// Список локаций хозяйств
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "10",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "companies_company_objects_list",    	// Список поднадзорных объектов
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "11",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "companies_set_company_objects_favorite",// Установка поднадзорных объектов в избранное
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "12",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "companies_unset_company_objects_favorite",  // Удаление поднадзорных объектов из избранного
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "13",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "directories_species",       			// Справочник видов животных
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "14",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "directories_breeds",        			// Справочник пород животных
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "15",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "directories_countries",     			// Справочник стран
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "16",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "directories_regions",       			// Справочник регионов
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "17",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "directories_districts",     			// Справочник районов
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "18",
                "role_slug"             => "doctor_company",                   		// Вет. врач хоз-ва
                "right_slug"            => "directories_keeping_purposes",     		// Справочник целей содержания
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "19",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "directories_keeping_types", 			// Справочник типов содержания
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "20",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "directories_tools_locations",       	// Справочник мест нанесения маркировки
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "21",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "directories_genders",       			// Справочник полов
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "22",
                "role_slug"             => "doctor_company",                   		// Вет. врач хоз-ва
                "right_slug"            => "directories_out_basises",   			// Справочник оснований выбытия
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "23",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "directories_out_types",     			// Справочник расходов выбытия
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "24",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "directories_mark_statuses", 			// Справочник статусов маркирования
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "25",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "directories_mark_tool_types",       	// Справочник типов средств маркирования
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "26",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "directories_mark_types",    			// Справочник типов маркирования
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "27",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "notifications_list",        			// Список уведомлений пользователя
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "28",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "notifications_data",        			// Полная информация по уведомлению
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "29",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "notifications_read_all",    			// Прочтение всех уведомлений
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "30",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "applications_list", 					// Список заявок
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "31",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "applications_data", 					// Полная информация по заявке
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "32",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "applications_animal_add",   			// Добавление животного в заявку
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "33",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "applications_status",       			// Изменение статуса заявки
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "34",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "applications_animal_delete",        	// Удаление животного из заявки
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "35",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "users_edit",        					// Редактирование пользователя
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "36",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "users_password_change",     			// Смена пароля
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "37",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "users_herriot_req_add",     			// Добавление реквизитов хорриота
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "38",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "users_send_requisites",     			// Отправка пользователю реквизитов входа
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "39",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "selex_login",       					// Авторизация пользователя Селекс
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "40",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "selex_send_animals",        			// Передача данных по животным
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "41",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "selex_get_animals", 					// Получение данных по животным
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "42",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "animals_list",      					// Список животных
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "43",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "animals_data",      					// Полная информация по животному
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "44",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "animals_mark_edit", 					// Редактирование средства маркирования
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "45",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "animals_mark_photo_edit",   			// Редактирование фотографии средства маркирования
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "46",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "animals_mark_photo_delete", 			// Удаление фотографии средства маркирования
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "47",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "animals_mark_edit_group",   			// Групповое редактирование средства маркирования
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "48",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "animals_animal_keeping_object_edit",    // Редактирование объекта содержания животного
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "49",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "animals_animal_birth_object_edit",  	// Редактирование объекта рождения животного
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "50",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "animals_animal_object_edit_group",  	// Групповое редактирование места рождения, содержания, типа и вида содержания
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "51",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "animals_animal_keeping_purpose_edit",   // Редактирование целей содержания
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "52",
                "role_slug"             => "doctor_company",                    	// Вет. врач хоз-ва
                "right_slug"            => "animals_animal_keeping_type_edit",  	// Редактирование видов содержания
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],


            /* === Вет. врач района === */
            [
                "role_right_id"         => "53",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "auth_login",        					// Авторизация
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "54",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "auth_logout",       					// Выход
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "55",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "auth_info", 							// Информация о себе
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "56",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "auth_set",  							// Установка хозяйства
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "57",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "auth_herriot_requisites",   			// Редактирование реквизитов хорриота
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "59",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "companies_data",    					// Данные хозяйства
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "60",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "companies_list",    					// Список хозяйств
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "61",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "companies_locations_list",  			// Список локаций хозяйств
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "62",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "companies_company_objects_list",    	// Список поднадзорных объектов
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "65",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "directories_species",       			// Справочник видов животных
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "66",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "directories_breeds",        			// Справочник пород животных
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "67",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "directories_countries",     			// Справочник стран
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "68",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "directories_regions",       			// Справочник регионов
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "69",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "directories_districts",     			// Справочник районов
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "70",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "directories_keeping_purposes",      	// Справочник целей содержания
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "71",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "directories_keeping_types", 			// Справочник типов содержания
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "72",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "directories_tools_locations",       	// Справочник мест нанесения маркировки
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "73",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "directories_genders",       			// Справочник полов
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "74",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "directories_out_basises",   			// Справочник оснований выбытия
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "75",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "directories_out_types",     			// Справочник расходов выбытия
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "76",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "directories_mark_statuses", 			// Справочник статусов маркирования
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "77",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "directories_mark_tool_types",       	// Справочник типов средств маркирования
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "78",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "directories_mark_types",    			// Справочник типов маркирования
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "79",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "notifications_list",        			// Список уведомлений пользователя
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "80",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "notifications_data",        			// Полная информация по уведомлению
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "81",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "notifications_read_all",    			// Прочтение всех уведомлений
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "82",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "applications_list", 					// Список заявок
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "83",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "applications_data", 					// Полная информация по заявке
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "85",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "applications_status",       			// Изменение статуса заявки
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "87",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "users_edit",        					// Редактирование пользователя
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "88",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "users_password_change",     			// Смена пароля
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "89",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "users_herriot_req_add",     			// Добавление реквизитов хорриота
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "90",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "users_send_requisites",     			// Отправка пользователю реквизитов входа
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "94",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "animals_list",      					// Список животных
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "95",
                "role_slug"             => "doctor_district",                   	// Вет. врач района
                "right_slug"            => "animals_data",      					// Полная информация по животному
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],


            /* === Вет. врач региона === */
            [
                "role_right_id"         => "105",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "auth_login",        					// Авторизация
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "106",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "auth_logout",       					// Выход
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "107",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "auth_info",								// Информация о себе
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "108",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "auth_set",  							// Установка хозяйства
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "109",
                "role_slug"             => "doctor_region",                    		// Вет. врач региона
                "right_slug"            => "auth_herriot_requisites",   			// Редактирование реквизитов хорриота
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "111",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "companies_data",    					// Данные хозяйства
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "112",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "companies_list",    					// Список хозяйств
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "113",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "companies_locations_list",  			// Список локаций хозяйств
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "114",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "companies_company_objects_list",    	// Список поднадзорных объектов
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "117",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "directories_species",       			// Справочник видов животных
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "118",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "directories_breeds",        			// Справочник пород животных
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "119",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "directories_countries",     			// Справочник стран
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "120",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "directories_regions",       			// Справочник регионов
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "121",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "directories_districts",     			// Справочник районов
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "122",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "directories_keeping_purposes",      	// Справочник целей содержания
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "123",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "directories_keeping_types", 			// Справочник типов содержания
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "124",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "directories_tools_locations",       	// Справочник мест нанесения маркировки
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "125",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "directories_genders",       			// Справочник полов
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "126",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "directories_out_basises",   			// Справочник оснований выбытия
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "127",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "directories_out_types",     			// Справочник расходов выбытия
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "128",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "directories_mark_statuses", 			// Справочник статусов маркирования
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "129",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "directories_mark_tool_types",       	// Справочник типов средств маркирования
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "130",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "directories_mark_types",    			// Справочник типов маркирования
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "131",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "notifications_list",        			// Список уведомлений пользователя
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "132",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "notifications_data",        			// Полная информация по уведомлению
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "133",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "notifications_read_all",    			// Прочтение всех уведомлений
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "134",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "applications_list", 					// Список заявок
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "135",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "applications_data", 					// Полная информация по заявке
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "137",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "applications_status",       			// Изменение статуса заявки
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "139",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "users_edit",        					// Редактирование пользователя
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "140",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "users_password_change",     			// Смена пароля
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "141",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "users_herriot_req_add",     			// Добавление реквизитов хорриота
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "142",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "users_send_requisites",     			// Отправка пользователю реквизитов входа
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "146",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "animals_list",      					// Список животных
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ],
            [
                "role_right_id"         => "147",
                "role_slug"             => "doctor_region",                     	// Вет. врач региона
                "right_slug"            => "animals_data",      					// Полная информация по животному
                "created_at"            => "2024-05-22 14:10:16.411453",
                "updated_at"            => "2024-08-07 06:15:27.15472"
            ]
        ]);

        DB::statement("SELECT setval('system.system_roles_rights_role_right_id_seq', (SELECT MAX(role_right_id) from system.system_roles_rights))");
    }
}
