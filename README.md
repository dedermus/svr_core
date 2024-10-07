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
`php artisan db:seed --class=Svr\Core\Seeders\DatabaseSeeder`
