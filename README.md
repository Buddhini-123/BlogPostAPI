After cloning the project,

1. Run "composer install"
2. create .env file
3. change the database credentials in the .env file

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=your db name
    DB_USERNAME=root
    DB_PASSWORD=your password

4. Run "php artisan key:generate"
5. Run "php artisan migrate"
6. Run "php artisan server"