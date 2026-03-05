<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
|--------------------------------------------------------------------------
| Whiteboard Presence Channel
|--------------------------------------------------------------------------
| Authorizes users for the whiteboard session. Only enrolled students,
| course instructors, and admins can join. Returns identity data for
| the presence member list.
*/
Broadcast::channel('whiteboard.{courseId}', function ($user, $courseId) {
    $isInstructor = $user->courses()->where('courses.id', $courseId)->exists();
    $isStudent    = $user->purchasedCourses()->contains('id', $courseId);
    $isAdmin      = $user->isAdmin();

    if ($isInstructor || $isStudent || $isAdmin) {
        return [
            'id'            => $user->id,
            'name'          => $user->full_name,
            'is_instructor' => $isInstructor || $isAdmin,
        ];
    }

    return false;
});
