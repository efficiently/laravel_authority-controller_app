<?php

$serializer = new SuperClosure\Serializer;
return [
    'initialize' => $serializer->serialize(function ($authority) {
        $user = auth()->guest() ? new App\User : $authority->getCurrentUser();
        if ($user->hasRole('admin')) {
            $authority->allow('manage', App\User::class);
        } else {
            // nothing
        }
    })
];
