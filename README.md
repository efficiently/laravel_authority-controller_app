[Laravel 5](http://laravel.com) sample application with [AuthorityController](https://github.com/efficiently/authority-controller/tree/2.0) package
=====================

The Laravel framework utilizes [Composer](http://getcomposer.org/) for installation and dependency management. If you haven't already, start by [installing Composer](http://getcomposer.org/doc/00-intro.md).

The easiest way to play with Authority-Controller, is to `git clone` this Laravel 5.0 demo application:
```bash
git clone https://github.com/efficiently/laravel_authority-controller_app --branch 2.0 && cd laravel_authority-controller_app/
```

Then inside the application's root, run these commands:
```bash
composer install
php artisan serve
```
In your Web browser, go to this URL: [http://localhost:8000/users](http://localhost:8000/users)

You should see an `AccessDenied` exception.

With this error message: __You are not authorized to access this page.__.

Go to: [http://localhost:8000/auth/login](http://localhost:8000/auth/login)

Fill the login form with `admin@localhost.com` and `password` then click on the `Login` button.

You should see: __You are logged in!__.

Then go back to: [http://localhost:8000/users](http://localhost:8000/users)

You should see: __Administrator__.

Congratulations, You have the basics to use `AuthorityController`!

You can read how this demo was build [HERE](demo_making_of.md).
