<?php

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
