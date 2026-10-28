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

/*
 * The attendance list is linked from the lessons table, which trainers can open
 * as well. Course-level access is scoped inside AttendenceController.
 */
Route::group(['middleware' => 'role:administrator|teacher'], function () {
    Route::get('/attendance-list/{course_id}/{lesson_id}', [AttendenceController::class,'attendance_list'])->name('attendance.attendance.list');

    Route::get('/attendance-get-data/{course_id}/{lesson_id}', [AttendenceController::class,'get_data'])->name('attendance.attendance.get_data');
});

Route::group(['middleware' => 'role:administrator'], function () {

 Route::get('employee', [EmployeeController::class, 'index'])->name('employee.index');

    Route::get('employee/sample', [EmployeeController::class, 'downloadSample'])
        ->name('employee.sample');

    Route::post('employee/import', [EmployeeController::class, 'import'])
        ->name('employee.import');

    Route::get('certificates/generate/{course_id}/{user_id}', [EmployeeController::class,'generateCertificate'])->name('certificates.generate');


    //======= External Courses =======/
    Route::get('payments', ['uses' => 'PaymentController@index', 'as' => 'admin.externalcourses.index']);

});

Route::get('feedback-form-create/{course_id}/{user_id}', [FeedbackController::class,'createFeedbackForm'])->name('feedback.create_feedback_form');
Route::post('feedback-form-submit', [FeedbackController::class,'feedbackSubmit'])->name('feedback.submit');

