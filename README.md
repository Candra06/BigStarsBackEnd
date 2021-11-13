

Backend For BigStar

### Langkah Deploy

- Lakukan git clone
- Run command ```composer require laravel/sanctum```
- Run command ```php artisan vendor:publish — provider="Laravel\Sanctum\SanctumServiceProvider"```
- Setting file .env (Edit dari .env.example)
- Create database
- Run ```php artisan migrate```
- Run command ```php artisan db:seed --class=UsersTableSeeder```
- Run command ```php artisan db:seed --class=GuruTableSeeder```
- Run command ```php artisan db:seed --class=WaliTableSeeder```
- Run command ```php artisan db:seed --class=SiswaTableSeeder```
- Run ```php artisan serve```
