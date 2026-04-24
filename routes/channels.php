<?php

use App\Models\Driver;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


Broadcast::channel('driver.{id}', function ($user, $id) {
    $driverId = Driver::where('userId', $user->id)->value('id');
    return $driverId != null && $driverId = $id;
});

Broadcast::channel('trip.{id}', function ($user, $id) {
    $driverId = Driver::where('userId', $user->id)->value('id');
    return $driverId != null && $driverId = $id;
});

Broadcast::channel('chat', function () {
    return true;
});
