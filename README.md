# laravel-9-api-with-auth-boilerplate

Laravel 9

1. Run composer update
2. Run composer install
3. Rename .env.example to .env
4. Run php artisan key:generate
5. Modify .env
   APP_NAME
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=myapp
   DB_USERNAME=root
   DB_PASSWORD=123456
6. Generate APP_KEY
7. Create email testing account, here I use mailtrap.io
8. Create Project Inbox and copy its setting to env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.mailtrap.io
   MAIL_PORT=2525
   MAIL_USERNAME=xxxxxxxxxxxxx
   MAIL_PASSWORD=xxxxxxxxxxxxx
   MAIL_ENCRYPTION=tls
9. Create the Database based on DB_DATABASE
10. php artisan migrate --seed
11. php artisan serve
12. Login with seeded user: admin@admin 123456
