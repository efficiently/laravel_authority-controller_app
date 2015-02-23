<?php

return [

    'initialize' => function ($authority) {
        $user = Auth::guest() ? new App\User : $authority->getCurrentUser();
        if ($user->hasRole('admin')) {
            $authority->allow('manage', 'App\User');
        } else {
            //nothing
        }
    }

];
