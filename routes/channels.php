<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('teacher.{id}', function ($user, $id) {
    $teacherProfile = $user->teacherProfile;
    if ($teacherProfile && (int) $teacherProfile->id === (int) $id) {
        return true;
    }
    return false;
});

Broadcast::channel('student.{id}', function ($user, $id) {
    return $user->role === 'student' && (int) $user->id === (int) $id;
});

Broadcast::channel('admin.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


Broadcast::channel('group.{target}', function ($user, $target) {
    // إذا كان الهدف المعلمين (teachers)، يجب أن يكون دور المستخدم معلم (teacher)
    if ($target === 'teachers') {
        return $user->role === 'teacher';
    }

    // إذا كان الهدف الطلاب (students)، يجب أن يكون دور المستخدم طالب (student)
    if ($target === 'students') {
        return $user->role === 'student';
    }

    // إذا كانت القناة للجميع
    if ($target === 'all') {
        return true;
    }

    // الوضع الافتراضي
    return $user->role === $target;
});
