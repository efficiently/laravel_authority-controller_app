[Laravel 4.1](http://laravel.com) sample application with [AuthorityController](https://github.com/efficiently/authority-controller) package
=====================

The Laravel framework utilizes [Composer](http://getcomposer.org/) for installation and dependency management. If you haven't already, start by [installing Composer](http://getcomposer.org/doc/00-intro.md).

Now you can install Laravel by issuing the following command from your terminal:
```bash
composer create-project laravel/laravel your-project-name --prefer-dist
```

Add `authority-controller` package to your `composer.json` file to require AuthorityController:
```bash
cd your-project-name/
composer require efficiently/authority-controller:1.1.*
```

Add the service provider to `app/config/app.php`:
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

In `app/config/database.php`
```php
  'default' => 'sqlite',
```

To create the users table we will use artisan, We hope you know how to use it. Let's init the migrate system and tell artisan to setup a user table. We are using additional parameters --table and --create, this way artisan builds a base class we can use.

Console:
```bash
php artisan migrate:make create_users_table --table=users --create
```

You can find the php file artisan has created under `app/database/migrations`.
The name of file depends on the date and time when it was created.

Let's define a migration that looks like this:
```php
<?php
//app/database/migrations/........_create_users_table.php:

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
      Schema::create('users', function(Blueprint $table) {
          $table->increments('id');
          $table->string('email')->unique();
          $table->string('name');
          $table->string('password');
          $table->string('remember_token', 100)->nullable();
          $table->timestamps();
      });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
      Schema::drop('users');
  }

}
```

Next, we can run our migrations from our terminal using the migrate command. Simply execute this command from the root of your project:
```bash
php artisan migrate
```

For an easy start, we should insert a default user in the table. To do this, we create the following file.
```php
<?php
//app/database/seeds/UsersTableSeeder.php:

class UsersTableSeeder extends Seeder {

    public function run()
    {
        // !!! All existing users are deleted !!!
        DB::table('users')->truncate();

        User::create([
            'email'     => 'admin@localhost',
            'name'  => 'Administrator',
            'password'  => Hash::make('password'),
        ]);
    }
}
```

This way we simply create an admin user with password as password. Now we have to tell the system to use this seeder. We declare a call to `UsersTableSeeder` in the `DatabaseSeeder`.

```php
<?php
//app/database/seeds/DatabaseSeeder.php:

class DatabaseSeeder extends Seeder {

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    Eloquent::unguard();

    $this->call('UsersTableSeeder');
  }

}
```

We have provided a basic table structure to get you started in creating your roles and permissions.

Run the Authority migrations
```bash
php artisan migrate --package=machuga/authority-l4
```

This will create the following tables

- roles
- role_user
- permissions

To utilize these tables, you can add the following methods to your `User` model (you can copy paste this [one](https://gist.githubusercontent.com/denvers/10828426/raw/530be8ffd4c6da5d363a3749b03b87ddad3a1702/User.php)). You will also need to create Role and Permission Model stubs.
```php
    //app/models/User.php
    public function roles()
    {
        return $this->belongsToMany('Role');
    }

    public function permissions()
    {
        return $this->hasMany('Permission');
    }

    public function hasRole($key)
    {
        foreach($this->roles as $role){
            if($role->name === $key)
            {
                return true;
            }
        }
        return false;
    }

    //app/models/Role.php
    <?php

    class Role extends Eloquent {}

    //app/models/Permission.php
    <?php
    class Permission extends Eloquent {}
```

For an easy start, we should insert a default role in the table. To do this, we create the following file.
```php
<?php
//app/database/seeds/RolesTableSeeder.php:

class RolesTableSeeder extends Seeder {

    public function run()
    {
        // !!! All existing roles are deleted !!!
        DB::table('role_user')->truncate();
        DB::table('roles')->truncate();

        $user = User::where('email', 'admin@localhost')->firstOrFail();
        $roleAdmin = Role::create(['name' => 'admin']);

        $user->roles()->attach($roleAdmin->id);
    }
}
```

This way we simply create an admin role and attach it to your admin user. Now we have to tell the system to use this seeder. We declare a call `RolesTableSeeder` in the `DatabaseSeeder`.
```php
<?php
//app/database/seeds/DatabaseSeeder.php:

class DatabaseSeeder extends Seeder {

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    Eloquent::unguard();

    $this->call('UsersTableSeeder');
    $this->call('RolesTableSeeder');
  }

}
```

To seed your database, you may use the db:seed command on the Artisan CLI:
```bash
php artisan db:seed
```

Views live in the `app/views` directory and contain the HTML of your application.
We need to create a layout for these views `layouts/application.blade.php`.

Let's create our main layout for this application, `layouts/application.blade.php` file:
```html
<html>
    <body>
        <div class="nav">
            <ul class="nav">
                @if ( Auth::guest() )
                    <li>{{ HTML::linkRoute('sessions.create', 'Login') }}</li>
                @else
                    <li>
                        {{ Form::open(['method' => 'DELETE', 'route' => ['sessions.destroy', Auth::user()->id]]) }}
                            {{ Form::submit('Logout', ['class' => 'btn btn-danger']) }}
                        {{ Form::close() }}
                    </li>
                @endif
            </ul>
        </div>

        <h1>Laravel and AuthorityController Quickstart</h1>

        {{-- Success-Messages --}}
        @if ($message = Session::get('success'))
            <div class="alert alert-success alert-block">
                {{{ $message }}}
            </div>
        @endif

        @yield('content')
    </body>
</html>
```

To start, we need a home page.

You need to update home route, in `app/routes.php` file, replace the line 16:
```php
  return View::make('hello');
```

With this one:
```php
  return View::make('home');
```

Then you need to create the file `app/views/home.blade.php`:
```html
@extends('layouts.application')

@section('content')
<h2>Welcome</h2>
@stop
```

Now, we need a controller to show the login page. We will name it SessionsController.

Create a RESTful controller `SessionsController` with only `create`, `store` and `destroy` actions via the command line, execute the following command:
```bash
php artisan controller:make SessionsController --only="create,store,destroy"
```

Now we can register a resourceful route to the controller, add the line below a the end of `app/routes.php` file:
```php
//...
Route::resource('sessions', 'SessionsController', ['only' => ['create', 'store', 'destroy']]);
```

Now let's modify our `SeesionsController` `create`, `store` and `destroy` actions to look like this:

```php
<?php
//app/controllers/SessionsController.php:

class SessionsController extends \BaseController {

  /**
   * Show the form for creating a new session.
   *
   * @return Response
   */
  public function create()
  {
      // Check if we already logged in
      if (Auth::check())
      {
        // Redirect to homepage
        return Redirect::to('')->with('success', 'You are already logged in');
      }

      // Show the login page
      return View::make('sessions.create');
  }

  /**
   * Store a newly created resource in session.
   *
   * @return Response
   */
  public function store()
  {
      // Get all the inputs
      // email is used for login and for validation to return correct error-strings
      $userdata = array(
          'email'       => Input::get('email'),
          'password' => Input::get('password')
      );

      // Declare the rules for the form validation.
      $rules = array(
          'email'  => 'Required',
          'password'  => 'Required'
      );

      // Validate the inputs.
      $validator = Validator::make($userdata, $rules);

      // Check if the form validates with success.
      if ($validator->passes())
      {
          // Try to log the user in.
          if (Auth::attempt($userdata))
          {
              // Redirect to homepage
              return Redirect::to('')->with('success', 'You have logged in successfully');
          }
          else
          {
              // Redirect to the login page.
              return Redirect::route('sessions.create')->withErrors(array('password' => 'Password invalid'))->withInput(Input::except('password'));
          }
      }

      // Something went wrong.
      return Redirect::route('sessions.create')->withErrors($validator)->withInput(Input::except('password'));
  }

  /**
   * Remove the specified resource from session.
   *
   * @param  int  $id
   * @return Response
   */
  public function destroy($id)
  {
      // Log out
      Auth::logout();

      // Redirect to homepage
      return Redirect::to('')->with('success', 'You are logged out');
  }

}
```

The function `create` of `SessionsController` just checks if we already logged in, otherwise it shows the login page.

To show a login page, we have to create it.

```html
{{-- app/views/sessions/create.blade.php --}}

@extends('layouts.application')

@section('content')
<div class="page-header">
    <h2>Login into your account</h2>
</div>

{{ Form::open(array('url' => route("sessions.store"), 'class' => 'form-horizontal')) }}

    <!-- Email -->
    <div class="control-group {{{ $errors->has('email') ? 'error' : '' }}}">
        {{ Form::label('email', 'E-Mail', array('class' => 'control-label')) }}

        <div class="controls">
            {{ Form::text('email', Input::old('email')) }}
            {{ $errors->first('email') }}
        </div>
    </div>

    <!-- Password -->
    <div class="control-group {{{ $errors->has('password') ? 'error' : '' }}}">
        {{ Form::label('password', 'Password', array('class' => 'control-label')) }}

        <div class="controls">
            {{ Form::password('password') }}
            {{ $errors->first('password') }}
        </div>
    </div>

    <!-- Login button -->
    <div class="control-group">
        <div class="controls">
            {{ Form::submit('Login', array('class' => 'btn')) }}
        </div>
    </div>

{{ Form::close() }}
@stop
```

User permissions are defined in an AuthorityController configuration file.

You can publish the AuthorityController default configuration file with the command below:
```bash
php artisan config:publish efficiently/authority-controller
```

Here a basic config sample. Users with admin role can do everything (read, update, delete) in User resource.
```php
<?php
//app/config/packages/efficiently/authority-controller/config.php

return [

    'initialize' => function($authority) {

         $user = Auth::guest() ? new User : $authority->getCurrentUser();
         if ($user->hasRole('admin')) {
             $authority->allow('manage', 'User');
         } else {
             //nothing
         }

    },

];
```

Init resource filters and controller methods.

In your `app/controllers/BaseController.php` file:
```php
class BaseController extends \Controller
{
    use Efficiently\AuthorityController\ControllerAdditions;
    //code...
}
```

Create a RESTful controller `UsersController` via the command line, execute the following command:
```bash
php artisan controller:make UsersController
```

Now we can register a resourceful route to the controller, add the line below a the end of `app/routes.php` file:
```php
//...
Route::resource('users', 'UsersController');
```

Next, we'll create a simple view to display our user data. We're going to place new folder and a view file in this folder: `users/index.blade.php`:
```html
@extends('layouts.application')

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
  //app/controllers/UsersController.php

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
      return View::make('users.index')->with('users', $this->users);
  }

  //...
```

Run Laravel development server, with this command:
```bash
php artisan serve
```

In your Web browser, go to this URL: http://localhost:8000/users

You should see an `Efficiently\AuthorityController\Exceptions\AccessDenied` exception.

With this error message: __You are not authorized to access this page.__.

Go to: http://localhost:8000/sessions/create

Fill the login form with `admin@localhost` and `password` then click on the Login button.

You should see: __You have logged in successfully__.

Then go to: http://localhost:8000/users

You should see: __Administrator__.

Congratulations, You have the basics to use `AuthorityController` package!

Credits
-------
[Laravel Official Docs](http://laravel.com/docs)

[Beni's Laravel 4 User Management Tutorial](https://bitbucket.org/beni/laravel-4-tutorial/wiki/User%20Management)
