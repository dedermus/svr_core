<?php

namespace Svr\Core\Traits;

trait GetDictionary
{
    /**
     * Статусы приложений
     * @return array[]
     */
    public static function DictionaryApplicationStatus(): array
    {
        return [
            'created'		=> [
                'status_slug'		=> 'created',
                'status_name'		=> 'Создано',
            ],
            'prepared'		=> [
                'status_slug'		=> 'prepared',
                'status_name'		=> 'Подготовлено'
            ],
            'sent'		=> [
                'status_slug'		=> 'sent',
                'status_name'		=> 'Отправлено'
            ],
            'complete_full'		=> [
                'status_slug'		=> 'complete_full',
                'status_name'		=> 'Завершено полностью'
            ],
            'complete_partial'		=> [
                'status_slug'		=> 'complete_partial',
                'status_name'		=> 'Завершено частично'
            ],
            'finished'		=> [
                'status_slug'		=> 'finished',
                'status_name'		=> 'Отработано'
            ]
        ];
    }

    /**
     * Системные статусы
     * @return array[]
     */
    public static function DictionarySystemStatus(): array
    {
        return [
            'enabled'		=> [
                'status_id'			=> 'enabled',
                'status_name'		=> 'Активен',
                'status_color'		=> 'success'
            ],
            'disabled'		=> [
                'status_id'			=> 'disabled',
                'status_name'		=> 'Заблокирован',
                'status_color'		=> 'warning'
            ]
        ];
    }

    /**
     * Статус животного
     * @return array[]
     */
    public static function DictionaryBreedingValue(): array
    {
        return [
            'UNDEFINED' => [
                'breeding_value_id' 	=> 'UNDEFINED',
                'breeding_value_name' 	=> 'Не определено'
            ],
            'BREEDING' => [
                'breeding_value_id' 	=> 'BREEDING',
                'breeding_value_name' 	=> 'Племенное'
            ],
            'NON_BREEDING' => [
                'breeding_value_id' 	=> 'NON_BREEDING',
                'breeding_value_name' 	=> 'Не племенное'
            ],
        ];
    }

    /**
     * Статусы заявок по животным
     * @return array[]
     */
    public static function DictionaryApplicationAnimalStatus(): array
    {
        return [
            'added' => [
                'application_animal_status_id' 		=> 'added',
                'application_animal_status_name' 	=> 'Добавлено'
            ],
            'in_application' => [
                'application_animal_status_id' 		=> 'in_application',
                'application_animal_status_name' 	=> 'В заявке'
            ],
            'sent' => [
                'application_animal_status_id' 		=> 'sent',
                'application_animal_status_name' 	=> 'Отправлено'
            ],
            'registered' => [
                'application_animal_status_id' 		=> 'registered',
                'application_animal_status_name' 	=> 'Зарегистрировано'
            ],
            'rejected' => [
                'application_animal_status_id' 		=> 'rejected',
                'application_animal_status_name' 	=> 'Отказано'
            ],
            'finished' => [
                'application_animal_status_id' 		=> 'finished',
                'application_animal_status_name' 	=> 'Завершено'
            ],
        ];
    }
}
