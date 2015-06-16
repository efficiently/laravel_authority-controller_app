Making of the [Laravel 5.1](http://laravel.com) sample application with [AuthorityController](https://github.com/efficiently/authority-controller) package
=====================

TL;DR
-----
You can get the demo application [HERE](https://github.com/efficiently/laravel_authority-controller_app#readme).

How to recreate this demo
-------------------------

The Laravel framework utilizes [Composer](http://getcomposer.org/) for installation and dependency management. If you haven't already, start by [installing Composer](http://getcomposer.org/doc/00-intro.md).

Now you can install Laravel by issuing the following command from your terminal:
```bash
composer create-project laravel/laravel your-project-name --prefer-dist
```

Add `authority-controller` package to your `composer.json` file to require AuthorityController:
```bash
cd your-project-name/
composer require efficiently/authority-controller:2.1.*
```

Add the service provider to `config/app.php`:
```php
    'Efficiently\AuthorityController\AuthorityControllerServiceProvider',
```

Add the aliases (facades) to your Laravel app config file:
```php
    'Params'    => 'Efficiently\AuthorityController\Facades\Params',
    'Authority' => 'Efficiently\AuthorityController\Facades\Authority',
```

Configure your application database, for this tutorial we use SQLite.

So you need to enable `php_sqlite3` and `php_pdo_sqlite` extensions in your `php.ini` file.

In `config/database.php`, replace `default' => 'mysql',` by `default' => 'sqlite','`:
```php
  'default' => 'sqlite',
```

Then you need to create the database file, execute this command from the root of your project:

```bash
touch storage/database.sqlite
```

To create the users table we will use artisan, We hope you know how to use it.

Next, we can run our migrations from our terminal using the migrate command. Simply execute this command from the root of your project:
```bash
php artisan migrate
```

For an easy start, we should insert a default user in the table. To do this, we create the following file `database/seeds/UserTableSeeder.php`:
```php
<?php
//database/seeds/UserTableSeeder.php:

use Illuminate\Database\Seeder;
use App\User;

class UserTableSeeder extends Seeder
{

    public function run()
    {
        // !!! All existing users are deleted !!!
        DB::table('users')->truncate();

        User::create([
            'email'     => 'admin@localhost.com',
            'name'  => 'Administrator',
            'password'  => Hash::make('password'),
        ]);
    }
}
```

This way we simply create an admin user with password as password. Now we have to tell the system to use this seeder. We uncomment the call to `UserTableSeeder` in the `DatabaseSeeder`.

```php
<?php
//database/seeds/DatabaseSeeder.php:

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call('UserTableSeeder');
    }
}
```

We have provided a basic table structure to get you started in creating your roles and permissions.

Publish them to your migrations directory or copy them directly.

```bash
php artisan vendor:publish --provider="Efficiently\AuthorityController\AuthorityControllerServiceProvider" --tag="migrations"
```

Run the migrations

```bash
php artisan migrate
```

This will create the following tables

- roles
- role_user
- permissions

To utilize these tables, you can add the following methods to your `App\User` model. You will also need to create Role and Permission Model stubs.
```php
    //app/User.php
    public function roles()
    {
        return $this->belongsToMany('App\Authority\Role');
    }

    public function permissions()
    {
        return $this->hasMany('App\Authority\Permission');
    }

    public function hasRole($key)
    {
        $hasRole = false;
        foreach ($this->roles as $role) {
            if ($role->name === $key) {
                $hasRole = true;
                break;
            }
        }

        return $hasRole;
    }

    //app/Authority/Role.php
    <?php namespace App\Authority;

    use Illuminate\Database\Eloquent\Model;

    class Role extends Model {}

    //app/Authority/Permission.php
    <?php namespace App\Authority;

    use Illuminate\Database\Eloquent\Model;

    class Permission extends Model {}
```

For an easy start, we should insert a default role in the table. To do this, we create the following file `database/seeds/RolesTableSeeder.php`:
```php
<?php
//database/seeds/RolesTableSeeder.php:

use Illuminate\Database\Seeder;
use App\User;
use App\Authority\Role;

class RolesTableSeeder extends Seeder
{

    public function run()
    {
        // !!! All existing roles are deleted !!!
        DB::table('role_user')->truncate();
        DB::table('roles')->truncate();

        $user = User::where('email', 'admin@localhost.com')->firstOrFail();
        $roleAdmin = Role::create(['name' => 'admin']);

        $user->roles()->attach($roleAdmin->id);
    }
}
```

This way we simply create an admin role and attach it to your admin user. Now we have to tell the system to use this seeder. We declare a call `RolesTableSeeder` in the `DatabaseSeeder`.
```php
<?php
//database/seeds/DatabaseSeeder.php:

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call('UserTableSeeder');
        $this->call('RolesTableSeeder');
    }
}
```

Composer need to know this two new Seeder classes, run this command:
```bash
composer dump-autoload
```

To seed your database, you may use the db:seed command on the Artisan CLI:
```bash
php artisan db:seed
```

User permissions are defined in an AuthorityController configuration file.

You can publish the AuthorityController default configuration file with the command below:

```bash
php artisan vendor:publish --provider="Efficiently\AuthorityController\AuthorityControllerServiceProvider" --tag="config"
```

Here a basic config sample. Users with admin role can do everything (create, read, update, delete) in User resource.
```php
<?php
//config/authority-controller.php

return [
    'initialize' => function($authority) {
        $user = Auth::guest() ? new App\User : $authority->getCurrentUser();
        if ($user->hasRole('admin')) {
            $authority->allow('manage', 'App\User');
        } else {
            //nothing
        }
    }
];
```

Edit your `app/Http/Controllers/Controller.php` file to add the `ControllerAdditions` trait:

```php
<?php namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller extends BaseController
{
    use DispatchesJobs, ValidatesRequests;
    use \Efficiently\AuthorityController\ControllerAdditions;

    //code...
}
```

Create a RESTful controller `UsersController` via the command line, execute the following command:
```bash
php artisan make:controller UsersController
```

Now we can register a resourceful route to the controller, add the line below a the end of `app/Http/routes.php` file:
```php
//...
Route::resource('users', 'UsersController');
```

Next, we'll create a simple view to display our user data. We're going to place a new view file `resources/views/users/index.blade.php`:
```html
@extends('app')

@section('content')
  <h2>Users list</h2>
  <ul>
  @foreach($users as $user)
    <li>{{{ $user->name }}}</li>
  @endforeach
  </ul>
@stop
```

Now let's modify our `UsersController` to add a constructor and complete `index` action to look like this:
```php
  //app/Http/Controllers/UsersController.php

  //...
  public function __construct()
  {
      $this->loadAndAuthorizeResource();
  }

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index()
  {
      return view('users.index')->with('users', $this->users);
  }

  //...
```

Run Laravel development server, with this command:
```bash
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

Credits
-------
[Laravel Official Docs](http://laravel.com/docs)
