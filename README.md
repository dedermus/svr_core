SVR CORE для Open-Admin
=========================

## Установка

```
$ composer require svr/core

$ php artisan migrate --path=vendor/svr/core/database/migrations

```

Миграции от 2024_01_01_000001 до 2024_01_01_000012. 

Все последующие должны быть новее

## Usage

[//]: # (See [wiki]&#40;http://open-admin.org/docs/en/extension-helpers&#41;)

License
------------

[//]: # (Licensed under [The MIT License &#40;GPL 3.0&#41;]&#40;LICENSE&#41;.)


Seeders
------------

`php artisan db:seed --class=Svr\Core\Seeders\DatabaseSeeder`


Lang
------------
`php artisan db:seed --class=Svr\Core\Seeders\DatabaseSeeder` svr-core-lang

Реализованы кастомные поля и методы:

### Кастомный вывод даты в колонке grid

'**xx_datetime**'

Example:
`
$grid->column('name', 'label')->display(function ($value) {return Carbon::parse($value);})->xx_datetime()->help($trans),
`
'xx_datetime' принимает строковый параметр формата даты. По умолчанию 'Y-m-d / H:i'

### Кастомный вывод даты и времени в show

'**xx_datetime**'

Example:
`
$show->field('name', 'label')->xx_datetime()
`

xx_datetime принимает строковый параметр формата даты. По умолчанию 'Y-m-d / H:i'

### Вывод подсказок для поля в show

'**xx_help**'

Example:
`
$show->field('name', 'label')->xx_help('help field message'),
`


### Валидация полей редактирования/создания с использованием JS Bootstrap

'**xx_input**'

Example:
`
$form->xx_input('country_name', 'country_name')->rules('max:100', ['max' => trans('validation.max')])->valid_bootstrap();
`

Вместо `text` используем 'xx_input'. Указываем ограничения `rules`. Если нужно что бы ограничения обрабатывались через js bootstrap, добавляем ->valid_bootstrap()

Текст сообщения берётся из `rules`.

Поддерживаются ограничения:
 - min
- max
- required
- regex

