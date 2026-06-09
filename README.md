### Installation and Configuration

##### Execute these commands below, in order

```
1. clone the project
```

```
1. composer install
```

```
2. cp .env-example .env
```

```
4. php artisan migrate --seed
```

```
4. php artisan shop:link
```

after that you must run the command to work queue

```
5. php artisan queue:listen
```

**Register for first time:**

> _http(s)://example.com/login_

#### steps to update

##### pull latest changes using git and run these commands

```
1. git pull origin master
```

```
2. composer i
```

```
2. php artisan optimize:clear
```

```
4. php artisan updater:after
```
# shop-pro
