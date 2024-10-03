SVR RAW для Open-Admin
=========================

## Установка

```
$ composer require svr/raw

$ php artisan migrate --path=vendor/svr/raw/database/migrations

```
## Добавление пунктов меню.
```
$ php artisan admin:import svr-raw

```

## Usage

[//]: # (See [wiki]&#40;http://open-admin.org/docs/en/extension-helpers&#41;)

License
------------

[//]: # (Licensed under [The MIT License &#40;GPL 3.0&#41;]&#40;LICENSE&#41;.)


## Permission.
Если пермиссий на роуты в БД нет (проверка по слагу), создаются через команду
```
$ php artisan migrate --path=vendor/svr/raw/database/migrations
```

## Пункты меню
Устанавливаются только если отсутствуют в БД. Проверка по uri. URI должен содержать в начале `raw`
