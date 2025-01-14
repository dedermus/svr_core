<?php

return [
    'required'    => 'Обязательное поле',
    'max'         => 'Максимальное количество символов: :max',
    'min'         => 'Минимальное количество символов: :min',
    'size'        => 'Должно быть :size символов',
    'email'       => 'Не верный email',
    'confirmed'   => 'Поле не совпадает',
    'date'        => 'Не верная дата',
    'date_format' => 'Не верный формат даты',
    'numeric'     => 'Не верное число',
    'integer'     => 'Не верное целое число',
    'between'     => 'Значение должно быть между :min и :max',
    'regex'       => 'Значение не совпадает с паттерном :regex',
    'unique'      => 'Параметр не является уникальным',
    'json'        => 'JSON в формате строки некорректен. Проверяемое поле должно быть допустимой строкой JSON.',
    'enum'        => 'Значение не входит в список допустимых',
    'string'      => 'Должна быть строка',
    'max_digits'  => 'Максимальное количество цифр должно быть: :max',
    'min_digits'  => 'Минимальное количество цифр должно быть: :min',
    'ip'          => 'Маска IP должна быть: 999.999.999.999',
    'exists'      => 'Указанный параметр не найден',
    'file'        => [
        'max' => 'Размер загружаемого файла больше :max Кб',
    ],
    'in'        => 'Переданное значение :input отсутствует в списке'
];
