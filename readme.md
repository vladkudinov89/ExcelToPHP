# Migration Excel => PHP #

### Getting started
Install the following packages prior to standing up your development environment:

- [Git](https://git-scm.com/)
- [docker](https://docs.docker.com/engine/installation/)
- [docker-compose](https://docs.docker.com/compose/install/)

Set your .env vars and then type:
```
git clone <this_repo>
cp .env.example .env
docker-compose up -d
docker-compose exec app composer install
```
## Usage

To start your containers you have only type next command:
```
make docker-up
```

To view migrated database you must visit web page phpmyadmin:
```
http://localhost:9191
```
Login is ``app`` and password is ``secret``.
In Database ``app`` you can find current table. 

## Testing project

To start test  you have only type next command:
```
docker-compose exec app php ./vendor/bin/phpunit