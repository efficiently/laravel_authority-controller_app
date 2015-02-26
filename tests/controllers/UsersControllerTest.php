<?php

use App\User;
use App\Role;
use Efficiently\AuthorityController\Authority;

class UsersControllerTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->seed();

        // Disables mass assignment exceptions from being thrown from model inserts and updates
        Eloquent::unguard();
    }

    public function tearDown()
    {
        parent::tearDown();
        // Renables any ability to throw mass assignment exceptions
        Eloquent::reguard();
    }

    // render index if have read authority on user
    public function testRenderIndexIfHaveReadAbilityOnUser()
    {
        $admin = User::where('name', 'Administrator')->firstOrFail();
        if (! $admin->hasRole('admin')) {
            $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
            $admin->roles()->attach($roleAdmin->id);
            $admin->load('roles'); // Reload the model to update the roles association, Eloquent come on!
        }

        $this->loginAs($admin);

        $response = $this->action('GET', "UsersController@index");
        $view = $response->original;
        $this->assertEquals($view->getName(), 'users.index');
        $this->assertViewHas('users');
    }

    // user should only destroy projects which he owns
    public function testUserCanOnlyDestroyProjectsWhichHeOwns()
    {
        $user = User::where('email', 'user@localhost.com')->first();
        if (is_null($user)) {
            $user = User::create([
                        'email'     => 'user@localhost.com',
                        'name'      => 'User',
                        'password'  => Hash::make('password'),
                    ]);
        }

        $authority = $this->loginAs($user);
        $authority->allow('destroy', 'App\User', function ($self, $user) {
            return $self->user()->id === $user->id;
        });
        $this->assertTrue($authority->can('destroy', new User(['id'=> $user->id])));
        $this->assertTrue($authority->cannot('destroy', new User));
    }

    /**
     * Set the currently logged in user for the application and load his authorization rules
     *
     * @param Illuminate\Contracts\Auth\Authenticatable $user
     * @param Closure $fn Function callback who contains Authority rules.
     *        By default it loads the default config file: 'config/authority-controller.php'.
     * @return Authority
     */
    protected function loginAs(Illuminate\Contracts\Auth\Authenticatable $user, $fn = null)
    {
        $this->app['auth']->login($user);
        $this->app['authority'] = new Authority($user);
        $authority = $this->app->make('authority');
        $fn = $fn ?: $this->app['config']->get('authority-controller.initialize');

        if ($fn) {
            $fn($authority);
        }

        return $authority;
    }
}
