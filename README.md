[Laravel 5.1](http://laravel.com) sample application with [AuthorityController](https://github.com/efficiently/authority-controller/tree/master) package
=====================

[![Build Status](https://travis-ci.org/efficiently/laravel_authority-controller_app.svg?branch=master)](https://travis-ci.org/efficiently/laravel_authority-controller_app)


For [**Laravel 4.2**](http://laravel.com/docs/4.2) supports, see the [1.0 branch](https://github.com/efficiently/laravel_authority-controller_app/tree/1.0) of this demo.

For [**Laravel 5.0**](http://laravel.com/docs/5.0) supports, see the [2.0 branch](https://github.com/efficiently/laravel_authority-controller_app/tree/2.0) of this demo.

The Laravel framework utilizes [Composer](http://getcomposer.org/) for installation and dependency management. If you haven't already, start by [installing Composer](http://getcomposer.org/doc/00-intro.md).

The easiest way to play with Authority-Controller, is to `git clone` this Laravel 5.1 demo application:
```bash
git clone https://github.com/efficiently/laravel_authority-controller_app && cd laravel_authority-controller_app/
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

Now, You can read the [doc](https://github.com/efficiently/authority-controller/blob/master/README.md#check-authority-rules--authorization) to add more authorization rules.

You can also read how this demo was build [HERE](demo_making_of.md).
