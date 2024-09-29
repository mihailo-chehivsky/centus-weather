# Centus Weather

**Warning about potentially harmful weather conditions.**

[Home page](http://localhost:8081)

## Installation

### Composer install

```shell
docker-compose run composer composer install
```

### Copy env file

```shell
cp .env.example .env
```

### Create Sail alias

```shell
alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'
```

### Sail up

```shell
sail up -d
```

### Run migrations

```shell
sail artisan migrate
```

## Cron jobs

### Run the scheduled commands

```cronexp
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Workers

### Processing jobs on the queue as a daemon

```shell
sail artisan queue:work
```

## Testing

### Run tests

```shell
sail artisan test
```
