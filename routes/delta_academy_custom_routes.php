<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Frontend\Auth\LoginController;
use App\Http\Controllers\Frontend\Auth\RegisterController;


use App\Http\Controllers\CoursesController;
use App\Http\Controllers\LessonsController;

use App\Http\Controllers\Backend\Admin\LessonsController as AdminLessonController;
use App\Http\Controllers\Backend\Admin\AttendenceController;
use App\Http\Controllers\Backend\Admin\EmployeeController;

use App\Http\Controllers\Backend\Admin\FeedbackController;


Route::group(['prefix'=>'register','as'=>'register.'],function(){
    Route::get('/course/{course_id?}', [CoursesController::class,'register_course'])->name('register.course');
    Route::post('/course', [CoursesController::class,'save_register_course'])->name('save.register.course');
});


Route::group(['prefix'=>'attendance','as'=>'attendance.'],function(){
    Route::get('/course/{course_id}/{lesson_id}', [LessonsController::class,'attendance_lesson'])->name('attendance.lesson');
    Route::post('/course', [LessonsController::class,'save_attendance_lesson'])->name('save.attendance.lesson');
});


Route::get('certificates/generate', [CoursesController::class,'generateCertificate'])->name('certificates.generate');

Route::group(['middleware' => 'role:administrator'], function () {

    Route::get('certificates/generate/{course_id}/{user_id}', [EmployeeController::class,'generateCertificate'])->name('certificates.generate');

    Route::get('/attendance-list/{course_id}/{lesson_id}', [AttendenceController::class,'attendance_list'])->name('attendance.attendance.list');
    
    Route::get('/attendance-get-data/{course_id}/{lesson_id}', [AttendenceController::class,'get_data'])->name('attendance.attendance.get_data');


    //======= External Courses =======/
    //Route::get('payments', ['uses' => 'PaymentController@index', 'as' => 'admin.externalcourses.index']);

});

Route::get('feedback-form-create/{course_id}/{user_id}', [FeedbackController::class,'createFeedbackForm'])->name('feedback.create_feedback_form');
Route::post('feedback-form-submit', [FeedbackController::class,'feedbackSubmit'])->name('feedback.submit');

