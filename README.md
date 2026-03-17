## The Web service app for applying to the repair service, admin panel to manage requests, built with Laravel 12, PHP 8,4 and Mysql 9 using Docker compose

## Technical Requirements. See details in [DECISIONS.md](https://github.com/sevarostov/repair-service-app/blob/master/DECISIONS.md) and [PROMTS.md](https://github.com/sevarostov/repair-service-app/blob/master/PROMTS.md)
[PHP 8.4](https://www.php.net/releases/8.4/en.php)
[Composer (System Requirements)](https://getcomposer.org/doc/00-intro.md#system-requirements)
[Laravel 12.54.1](https://laravel.com/docs/12.x)
[MySQL 9.6.0](https://hub.docker.com/r/mysql/mysql-server#!)
[Testing: PHPUnit 11.5.55](https://docs.phpunit.de/)
[Containerization: Docker 24.* + Docker Compose 2.*](https://www.docker.com)
[spatie/laravel-permission](https://github.com/spatie/laravel-permission)

## Installation

git clone https://github.com/sevarostov/repair-service-app.git

#### Copy file `.env.example` to `.env`
```
cp .env.example .env
```

#### Make Composer install the project's dependencies into vendor/ directory

```
composer install
```

## Generate key
```
php artisan key:generate
```

## Build the project

```
docker build -t php:latest --file ./docker/php/Dockerfile --target php ./docker
```

## Docker compose:
```
docker compose up -d
docker compose down
```

## Create database schema

```
docker exec -i php php artisan migrate
```

## Seed fixures data
````
docker exec php php artisan db:seed
````
This cmd creates and saves to db:

- app users:

**login**:`dispatcher@example.com`
**password**:`dispatcher_pwd`

Has roles `dispatcher`

**login**:`master@example.com`
**password**:`master_pwd`

Has role `master`

- requests.

## UI
`npm install && npm run dev`

## The project is available at `http://localhost`


## Secured area (needs login with credentials above):
[GET /login] login page
[GET /home] free access to all authorized users (roles no matter)
[GET|HEAD  requests] RequestController@index request list with filters and operations with requests (granted access for role `dispatcher|master`)
[POST      requests] RequestController@store (granted access for role `dispatcher|master`)
[GET|HEAD  requests/create] request creation (granted access for role `dispatcher|master`)
[POST      requests/{id}/assign] assign a User to Request and update status of request (e.g. `new`->`assigned`, granted access for role `dispatcher`)
[PATCH     requests/{id}/cancel] update status of request (e.g. `new`->`cancelled`, granted access for role `dispatcher`)
[PATCH     requests/{id}/take] update status of request (e.g. `assigned`->`in_progress`, granted access for role `master`)
[PATCH     requests/{id}/done] update status of request (e.g. `in_progress`->`done`, granted access for role `master`)

## Run tests

```
docker exec php vendor/bin/phpunit
```

## Manual checking parallel requests

`docker exec php php artisan app:parallel-check-command`
