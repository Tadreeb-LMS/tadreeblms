<?php
\Log::info('routes/channels.php LOADED');

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
    \Log::info('Authorizing Whiteboard Channel', ['user' => $user->id, 'course' => $courseId]);
    // Allow access to the Sandbox Test Whiteboard (Course ID 0)
    if ((int) $courseId === 0) {
        \Log::info('Whiteboard Channel Authorized for Sandbox');
        return [
            'id'            => $user->id,
            'name'          => $user->full_name,
            'is_instructor' => true,
        ];
    }

    $isInstructor = $user->courses()->where('courses.id', $courseId)->exists();
    $isStudent    = $user->purchasedCourses()->where('courses.id', $courseId)->exists();
    $isAdmin      = clone $user;
    $isAdmin      = method_exists($isAdmin, 'isAdmin') ? $isAdmin->isAdmin() : false;

    \Log::info('Whiteboard Channel Authorization Result', [
        'isInstructor' => $isInstructor,
        'isStudent'    => $isStudent,
        'isAdmin'      => $isAdmin
    ]);

    if ($isInstructor || $isStudent || $isAdmin) {
        return [
            'id'            => $user->id,
            'name'          => $user->full_name,
            'is_instructor' => $isInstructor || $isAdmin,
        ];
    }

    \Log::warning('Whiteboard Channel Authorization DENIED');
    return false;
});
