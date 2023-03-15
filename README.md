[![Latest Stable Version](https://poser.pugx.org/kky30/laravel-make-serivce/version)](https://packagist.org/packages/kky30/laravel-make-service)
[![Total Downloads](https://poser.pugx.org/kky30/laravel-make-service/downloads)](https://packagist.org/packages/kky30/laravel-make-service)
[![Latest Unstable Version](https://poser.pugx.org/kky30/laravel-make-service/v/unstable)](//packagist.org/packages/kky30/laravel-make-service)
[![License](https://poser.pugx.org/kky30/laravel-make-service/license)](https://packagist.org/packages/kky30/laravel-make-service)
# artisan make:service command for Laravel 9+
A simple package for adding `php artisan make:service` command to Laravel 9+

## Installation

```
composer require kky30/laravel-make-service --dev
```
## Usage
`php artisan make:service {name} {--F|force} {--M|model} {--N|model-name=default}` 

### Create plain Service:
```
php artisan make:service User
```
```php
<?php
namespace App\Services;

class UserService
{
}
```

### Create Service with inject model:

if you want to inject model to service, you can use `-M` or `--model` option.

or you can use `-N` or `--model-name` option to specify the model name.


```
php artisan make:service User -M
```
```php
<?php
namespace App\Services;

use App\Models\User;

class UserService
{
    public function __construct(protected User $user){}
}
```
or
```
php artisan make:service User -N UserGroup
```
```php
<?php
namespace App\Services;

use App\Models\$userGroup;

class UserService
{
    public function __construct(protected UserGroup $userGroup){}
}
```

### Force create Service:
if you want to force create service, you can use `-F` or `--force` option. 

__overwrite the existing service file.__
```
php artisan make:service User -F
```

## License
This package is an open-sourced software licensed under the MIT license.


