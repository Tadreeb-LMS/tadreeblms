<?php

use App\Http\Controllers\Backend\Admin\TestsController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\Admin\RolesController;
use App\Http\Controllers\Backend\Auth\User\AccountController;
use App\Http\Controllers\Backend\Auth\User\ProfileController;
use \App\Http\Controllers\Backend\Auth\User\UpdatePasswordController;
use \App\Http\Controllers\Backend\Auth\User\UserPasswordController;
use App\Http\Controllers\UserCourseRequestController;
use FontLib\Table\Type\name;

/*
 * All route names are prefixed with 'admin.'.
 */

// Data fixing.
Route::get('fix-data', [DashboardController::class, 'fix_data']);
Route::get('fix-attendance-data', [DashboardController::class, 'fix_attendance_data']);
Route::get('fix_assign_courses_data', [DashboardController::class, 'fix_assign_courses_data']);
Route::get('fix_assign_test_ans_data', [DashboardController::class, 'fix_assign_test_ans_data']);



//===== General Routes Here =====//
Route::redirect('/', '/user/dashboard', 301);
Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('setvaluesession/{type}', [DashboardController::class, 'setvaluesession'])->name('setvaluesession');

Route::group(['middleware' => ['role:administrator']], function () {
    Route::resource('roles', 'Admin\RolesController');
});

Route::group(['middleware' => 'role:teacher|administrator'], function () {
    Route::resource('orders', 'Admin\OrderController');
});
Route::group(['middleware' => 'permission:trainer_access'], function () {

    //===== Teachers Routes =====//
    Route::resource('teachers', 'Admin\TeachersController');
    Route::get('get-teachers-data', ['uses' => 'Admin\TeachersController@getData', 'as' => 'teachers.get_data']);
    Route::post('teachers_mass_destroy', ['uses' => 'Admin\TeachersController@massDestroy', 'as' => 'teachers.mass_destroy']);
    Route::post('teachers_restore/{id}', ['uses' => 'Admin\TeachersController@restore', 'as' => 'teachers.restore']);
    Route::delete('teachers_perma_del/{id}', ['uses' => 'Admin\TeachersController@perma_del', 'as' => 'teachers.perma_del']);
    Route::post('teacher/status', ['uses' => 'Admin\TeachersController@updateStatus', 'as' => 'teachers.status']);

    //===== Assessments Routes =====//
    Route::resource('assessment_accounts', 'Admin\AssessmentAccountsController');
    Route::post('assessment_accounts/status', ['uses' => 'Admin\AssessmentAccountsController@updateStatus', 'as' => 'assessment_accounts.status']);
    Route::get('assessment_accounts/assignments/{id}/{type}', ['uses' => 'Admin\AssessmentAccountsController@assignments', 'as' => 'assessment_accounts.account_assignments']);
    Route::get('assessment_accounts/assignments/create/{id}', ['uses' => 'Admin\AssessmentAccountsController@assignment_create', 'as' => 'assessment_accounts.account_assignment_create']);

    Route::get('assessment_accounts/assignment_question_answers/{user_id}/{assignment_id}', ['uses' => 'Admin\AssessmentAccountsController@assignment_question_answers', 'as' => 'assessment_accounts.assignment_question_answers']);
    Route::get('assignments', ['uses' => 'Admin\AssessmentAccountsController@assignments', 'as' => 'assessment_accounts.assignments']);
    Route::get('assignments/create', ['uses' => 'Admin\AssessmentAccountsController@assignment_create', 'as' => 'assessment_accounts.assignment_create']);
    
    Route::get('assignments-nc/create', 'Admin\AssessmentAccountsController@assignment_create_without_course')->name('assessment_accounts.assignment_create_without_course');

    Route::post('assessment_accounts/assignments/store', ['uses' => 'Admin\AssessmentAccountsController@assignment_store', 'as' => 'assessment_accounts.assignment_store']);

    Route::get('assignments_delete/{id}', 'Admin\AssessmentAccountsController@assignments_delete')->name('assessment_accounts.assignments_delete');


    Route::post('assessment_accounts/submit_result', ['uses' => 'Admin\AssessmentAccountsController@submit_result', 'as' => 'assessment_accounts.submit_result']);
    Route::post('assessment_accounts/question_solution', ['uses' => 'Admin\AssessmentAccountsController@question_solution', 'as' => 'assessment_accounts.question_solution']);

    // for new create

    Route::get('assessment_accounts/new_assisment/create', 'Admin\AssessmentAccountsController@create_new_assisment')->name('assessment_accounts.new-assisment');
    Route::get('asmnt/0/withcourse', 'Admin\AssessmentAccountsController@assignment_create_with_course')->name('asmnt_0_withcourse');

    Route::get('add-invitation-assingment', 'Admin\AssessmentAccountsController@assignment_create_invitaion_course')->name('add_asmnt_invitation');

    Route::get('assessment_accounts/assignments_with_course/{id}', ['uses' => 'Admin\AssessmentAccountsController@assignment_create', 'as' => 'assessment_accounts.assignments_with_course']);

    Route::resource('manual-assessments', 'Admin\ManualAssessmentController');
    Route::get('view-manual-assessment-answers/{id}', 'Admin\ManualAssessmentController@viewUserManualAsssessementAnswers');
    Route::get('send-reminder/{id}', 'Admin\ManualAssessmentController@sendReminder');
    Route::post('send-reminder/{id}', 'Admin\ManualAssessmentController@sendReminderPost');
    Route::get('send-reminder-all-users', 'Admin\ManualAssessmentController@sendReminderAllUsers');


    Route::get('final-submit', 'Admin\AssessmentAccountsController@final_submit')->name('assessment_accounts.final-submit');
    Route::post('final-submit-store', 'Admin\AssessmentAccountsController@final_submit_store')->name('assessment_accounts.final-submit-store');
    Route::post('course-assignment', 'Admin\AssessmentAccountsController@course_assignment')->name('assessment_accounts.course-assignment');

    Route::post('course-assignment-invitation', 'Admin\AssessmentAccountsController@course_assignment_invitation')->name('assessment_accounts.course-assignment-invitation');

    Route::get('course-assign-list', 'Admin\AssessmentAccountsController@course_assign_list')->name('assessment_accounts.course-assign-list');

    Route::get('course-invitation-list', 'Admin\AssessmentAccountsController@course_invitation_list')->name('assessment_accounts.course-invitation-list');
    
    Route::get('course_assign_delete/{id}', 'Admin\AssessmentAccountsController@course_assign_delete')->name('assessment_accounts.course_assign_delete');

    Route::get('course-requests', [UserCourseRequestController::class, 'courseRequests']);


    Route::get('course_assign_edit/{id}', 'Admin\AssessmentAccountsController@course_assign_edit')->name('assessment_accounts.course_assign_edit');
    Route::post('course_assignment_update', 'Admin\AssessmentAccountsController@course_assignment_update')->name('assessment_accounts.course_assignment_update');

    //===== Employee Routes =====//

    Route::get('employee', 'Admin\EmployeeController@index')->name('employee.index');
    Route::get('external-employee', 'Admin\EmployeeController@externalIndex')->name('employee.external_index');
    Route::get('employee/create', 'Admin\EmployeeController@create')->name('employee.create');
    Route::post('employee/store', 'Admin\EmployeeController@store')->name('employee.store');
    Route::get('employee/{id}', 'Admin\EmployeeController@show')->name('employee.show');
    Route::get('employee/edit/{id}', 'Admin\EmployeeController@edit')->name('employee.edit');
    Route::delete('employee/destroy/{id}', 'Admin\EmployeeController@destroy')->name('employee.destroy');
    Route::post('employee/update/{id}', 'Admin\EmployeeController@update')->name('employee.update');
    Route::post('employee/import', 'Admin\EmployeeController@import')->name('employee.import');
    Route::get('employee/external/create', 'Admin\EmployeeController@external_employee_create')->name('employee.external.create');
    Route::post('employee/external/store', 'Admin\EmployeeController@external_employee_store')->name('employee.external.store');

    Route::get('get-employee-data', ['uses' => 'Admin\EmployeeController@getData', 'as' => 'employee.get_data']);
    Route::get('get-external-data', ['uses' => 'Admin\EmployeeController@getExternalData', 'as' => 'employee.get_external_data']);

    Route::post('employee_mass_destroy', ['uses' => 'Admin\EmployeeController@massDestroy', 'as' => 'employee.mass_destroy']);
    Route::post('employee_restore/{id}', ['uses' => 'Admin\EmployeeController@restore', 'as' => 'employee.restore']);
    Route::delete('employee_perma_del/{id}', ['uses' => 'Admin\EmployeeController@perma_del', 'as' => 'employee.perma_del']);
    Route::post('employee/status', ['uses' => 'Admin\EmployeeController@updateStatus', 'as' => 'employee.status']);

    Route::get('employee/reset_pass/{id}', 'Admin\EmployeeController@reset_pass')->name('employee.reset-pass');
    Route::post('employee/change-password', 'Admin\EmployeeController@changepassword')->name('employee.change-password');

    //===== FORUMS Routes =====//
    Route::resource('forums-category', 'Admin\ForumController');
    Route::get('forums-category/status/{id}', 'Admin\ForumController@status')->name('forums-category.status');


    //===== Orders Routes =====//
    Route::get('get-orders-data', ['uses' => 'Admin\OrderController@getData', 'as' => 'orders.get_data']);
    Route::post('orders_mass_destroy', ['uses' => 'Admin\OrderController@massDestroy', 'as' => 'orders.mass_destroy']);
    Route::post('orders/complete', ['uses' => 'Admin\OrderController@complete', 'as' => 'orders.complete']);
    Route::delete('orders_perma_del/{id}', ['uses' => 'Admin\OrderController@perma_del', 'as' => 'orders.perma_del']);


    //===== Settings Routes =====//
    Route::get('settings/general', ['uses' => 'Admin\ConfigController@getGeneralSettings', 'as' => 'general-settings']);

    Route::post('settings/general', ['uses' => 'Admin\ConfigController@saveGeneralSettings'])->name('general-settings');

    Route::post('settings/contact', ['uses' => 'Admin\ConfigController@saveGeneralSettings'])->name('general-contact');

    Route::get('settings/social', ['uses' => 'Admin\ConfigController@getSocialSettings'])->name('social-settings');

    Route::post('settings/social', ['uses' => 'Admin\ConfigController@saveSocialSettings'])->name('social-settings');

    Route::get('contact', ['uses' => 'Admin\ConfigController@getContact'])->name('contact-settings');

    Route::get('footer', ['uses' => 'Admin\ConfigController@getFooter'])->name('footer-settings');

    Route::get('newsletter', ['uses' => 'Admin\ConfigController@getNewsletterConfig'])->name('newsletter-settings');

    Route::post('newsletter/sendgrid-lists', ['uses' => 'Admin\ConfigController@getSendGridLists'])->name('newsletter.getSendGridLists');

    Route::get('settings/zoom', ['uses' => 'Admin\ConfigController@getZoomSettings'])->name('zoom-settings');

    Route::post('settings/zoom', ['uses' => 'Admin\ConfigController@saveZoomSettings'])->name('zoom-settings');
    Route::post('test', ['uses' => 'Admin\ConfigController@saveZoomSettings'])->name('zoom-settings');


    //===== Slider Routes =====/
    Route::resource('sliders', 'Admin\SliderController');
    Route::get('sliders/status/{id}', 'Admin\SliderController@status')->name('sliders.status', 'id');
    Route::post('sliders/save-sequence', ['uses' => 'Admin\SliderController@saveSequence', 'as' => 'sliders.saveSequence']);
    Route::post('sliders/status', ['uses' => 'Admin\SliderController@updateStatus', 'as' => 'sliders.status']);


    //===== Sponsors Routes =====//
    Route::resource('sponsors', 'Admin\SponsorController');
    Route::get('get-sponsors-data', ['uses' => 'Admin\SponsorController@getData', 'as' => 'sponsors.get_data']);
    Route::post('sponsors_mass_destroy', ['uses' => 'Admin\SponsorController@massDestroy', 'as' => 'sponsors.mass_destroy']);
    Route::get('sponsors/status/{id}', 'Admin\SponsorController@status')->name('sponsors.status', 'id');
    Route::post('sponsors/status', ['uses' => 'Admin\SponsorController@updateStatus', 'as' => 'sponsors.status']);

    //===== Testimonials Routes =====//
    Route::resource('testimonials', 'Admin\TestimonialController');
    Route::get('get-testimonials-data', ['uses' => 'Admin\TestimonialController@getData', 'as' => 'testimonials.get_data']);
    Route::post('testimonials_mass_destroy', ['uses' => 'Admin\TestimonialController@massDestroy', 'as' => 'testimonials.mass_destroy']);
    Route::get('testimonials/status/{id}', 'Admin\TestimonialController@status')->name('testimonials.status', 'id');
    Route::post('testimonials/status', ['uses' => 'Admin\TestimonialController@updateStatus', 'as' => 'testimonials.status']);


    //===== FAQs Routes =====//
    Route::resource('faqs', 'Admin\FaqController');
    Route::get('get-faqs-data', ['uses' => 'Admin\FaqController@getData', 'as' => 'faqs.get_data']);
    Route::post('faqs_mass_destroy', ['uses' => 'Admin\FaqController@massDestroy', 'as' => 'faqs.mass_destroy']);
    Route::get('faqs/status/{id}', 'Admin\FaqController@status')->name('faqs.status');
    Route::post('faqs/status', ['uses' => 'Admin\FaqController@updateStatus', 'as' => 'faqs.status']);


    //====== Contacts Routes =====//
    Route::resource('contact-requests', 'ContactController');
    Route::get('get-contact-requests-data', ['uses' => 'ContactController@getData', 'as' => 'contact_requests.get_data']);


    //====== Tax Routes =====//
    Route::resource('tax', 'TaxController');
    Route::get('tax/status/{id}', 'TaxController@status')->name('tax.status', 'id');
    Route::post('tax/status', 'TaxController@updateStatus')->name('tax.status');


    //====== Coupon Routes =====//
    Route::resource('coupons', 'CouponController');
    Route::get('coupons/status/{id}', 'CouponController@status')->name('coupons.status', 'id');
    Route::post('coupons/status', 'CouponController@updateStatus')->name('coupons.status');


    //==== Remove Locale FIle ====//
    Route::post('delete-locale', function () {
        \Barryvdh\TranslationManager\Models\Translation::where('locale', request('locale'))->delete();

        \Illuminate\Support\Facades\File::deleteDirectory(public_path('../resources/lang/' . request('locale')));
    })->name('delete-locale');


    //==== Update Theme Routes ====//
    Route::get('update-theme', 'UpdateController@index')->name('update-theme');
    Route::post('update-theme', 'UpdateController@updateTheme')->name('update-files');
    Route::post('list-files', 'UpdateController@listFiles')->name('list-files');
    Route::get('backup', 'BackupController@index')->name('backup');
    Route::get('generate-backup', 'BackupController@generateBackup')->name('generate-backup');

    Route::post('backup', 'BackupController@storeBackup')->name('backup.store');


    //===Trouble shoot ====//
    Route::get('troubleshoot', 'Admin\ConfigController@troubleshoot')->name('troubleshoot');


    //==== API Clients Routes ====//
    Route::prefix('api-client')->group(function () {
        Route::get('all', 'Admin\ApiClientController@all')->name('api-client.all');
        Route::post('generate', 'Admin\ApiClientController@generate')->name('api-client.generate');
        Route::post('status', 'Admin\ApiClientController@status')->name('api-client.status');
    });


    //==== Sitemap Routes =====//
    Route::get('sitemap', 'SitemapController@getIndex')->name('sitemap.index');
    Route::post('sitemap', 'SitemapController@saveSitemapConfig')->name('sitemap.config');
    Route::get('sitemap/generate', 'SitemapController@generateSitemap')->name('sitemap.generate');


    Route::post('translations/locales/add', 'LangController@postAddLocale');
    Route::post('translations/locales/remove', 'LangController@postRemoveLocaleFolder')->name('delete-locale-folder');
});


//Common - Shared Routes for Teacher and Administrator
Route::group(['middleware' => 'role:administrator|teacher'], function () {

    //====== Reports Routes =====//
    Route::get('report/sales', ['uses' => 'ReportController@getSalesReport', 'as' => 'reports.sales']);
    Route::get('report/students', ['uses' => 'ReportController@getStudentsReport', 'as' => 'reports.students']);

    Route::get('get-course-reports-data', ['uses' => 'ReportController@getCourseData', 'as' => 'reports.get_course_data']);
    Route::get('get-bundle-reports-data', ['uses' => 'ReportController@getBundleData', 'as' => 'reports.get_bundle_data']);
    Route::get('get-subscribe-reports-data', ['uses' => 'ReportController@getSubscibeData', 'as' => 'reports.get_subscribe_data']);
    Route::get('get-students-reports-data', ['uses' => 'ReportController@getStudentsData', 'as' => 'reports.get_students_data']);


    //====== Wallet  =====//
    Route::get('payments', ['uses' => 'PaymentController@index', 'as' => 'payments']);
    Route::get('get-earning-data', ['uses' => 'PaymentController@getEarningData', 'as' => 'payments.get_earning_data']);
    Route::get('get-withdrawal-data', ['uses' => 'PaymentController@getwithdrawalData', 'as' => 'payments.get_withdrawal_data']);
    Route::get('payments/withdraw-request', ['uses' => 'PaymentController@createRequest', 'as' => 'payments.withdraw_request']);
    Route::post('payments/withdraw-store', ['uses' => 'PaymentController@storeRequest', 'as' => 'payments.withdraw_store']);
    Route::get('payments-requests', ['uses' => 'PaymentController@paymentRequest', 'as' => 'payments.requests']);
    Route::get('get-payment-request-data', ['uses' => 'PaymentController@getPaymentRequestData', 'as' => 'payments.get_payment_request_data']);
    Route::post('payments-request-update', ['uses' => 'PaymentController@paymentsRequestUpdate', 'as' => 'payments.payments_request_update']);


    Route::get('menu-manager', ['uses' => 'MenuController@index'])->name('menu-manager');
});

Route::post('dashboard-status', ['uses' => 'DashboardController@getDashboardStats', 'as' => 'dashboard.stats']);


//===== Categories Routes =====//
Route::resource('categories', 'Admin\CategoriesController');
Route::get('get-categories-data', ['uses' => 'Admin\CategoriesController@getData', 'as' => 'categories.get_data']);
Route::post('categories_mass_destroy', ['uses' => 'Admin\CategoriesController@massDestroy', 'as' => 'categories.mass_destroy']);
Route::post('categories_restore/{id}', ['uses' => 'Admin\CategoriesController@restore', 'as' => 'categories.restore']);
Route::delete('categories_perma_del/{id}', ['uses' => 'Admin\CategoriesController@perma_del', 'as' => 'categories.perma_del']);


//===== Courses Routes =====//
Route::resource('courses', 'Admin\CoursesController');


Route::post('get-courses-by-lang', ['uses' => 'Admin\AssessmentAccountsController@getCoursesByLanguage', 'as' => 'get.courses.by_lang']);

Route::post('get-courses-by-course-type', ['uses' => 'Admin\AssessmentAccountsController@getCoursesByCourseType', 'as' => 'get.courses.by_course_type']);

Route::get('get-courses-data', ['uses' => 'Admin\CoursesController@getData', 'as' => 'courses.get_data']);
Route::post('courses_mass_destroy', ['uses' => 'Admin\CoursesController@massDestroy', 'as' => 'courses.mass_destroy']);
Route::post('courses_restore/{id}', ['uses' => 'Admin\CoursesController@restore', 'as' => 'courses.restore']);
Route::delete('courses_perma_del/{id}', ['uses' => 'Admin\CoursesController@perma_del', 'as' => 'courses.perma_del']);
Route::post('course-save-sequence', ['uses' => 'Admin\CoursesController@saveSequence', 'as' => 'courses.saveSequence']);
Route::get('course-publish/{id}', ['uses' => 'Admin\CoursesController@publish', 'as' => 'courses.publish']);
Route::get('get-cms-data', 'Admin\CoursesController@getCmsData')->name('courses.get-cms-data');
Route::get('cms-course', 'Admin\CoursesController@cmsCourse')->name('courses.cms-course');
Route::get('exportCourseAsCsv', 'Admin\CoursesController@exportCourseAsCsv');
Route::get('/record-attendance/{slug}', 'Admin\CoursesController@recordAttendance');
Route::resource('learning-pathways', 'Admin\LearningPathwayController');
Route::resource('pathway-assignments', 'Admin\PathwayAssignmentController');
Route::get('learning-pathways/manage-users/{id}', 'Admin\LearningPathwayController@manageUsers');
Route::post('learning-pathways/manage-users/{id}', 'Admin\LearningPathwayController@manageUsersPost');
Route::resource('assign-learning-pathways', 'Admin\AssignLearningPathwayController');



//===== Bundles Routes =====//
Route::resource('bundles', 'Admin\BundlesController');
Route::get('get-bundles-data', ['uses' => 'Admin\BundlesController@getData', 'as' => 'bundles.get_data']);
Route::post('bundles_mass_destroy', ['uses' => 'Admin\BundlesController@massDestroy', 'as' => 'bundles.mass_destroy']);
Route::post('bundles_restore/{id}', ['uses' => 'Admin\BundlesController@restore', 'as' => 'bundles.restore']);
Route::delete('bundles_perma_del/{id}', ['uses' => 'Admin\BundlesController@perma_del', 'as' => 'bundles.perma_del']);
Route::post('bundle-save-sequence', ['uses' => 'Admin\BundlesController@saveSequence', 'as' => 'bundles.saveSequence']);
Route::get('bundle-publish/{id}', ['uses' => 'Admin\BundlesController@publish', 'as' => 'bundles.publish']);


//===== Lessons Routes =====//
Route::resource('lessons', 'Admin\LessonsController');
Route::resource('course-feedback-questions', 'Admin\CourseFeebackController');
Route::get('course-feedback-questions/delete/{id}', 'Admin\CourseFeebackController@destroy');
Route::get('course-feedback-questions/edit/{id}', 'Admin\CourseFeebackController@edit')->name('course.coursefeedbackquestion.edit');
Route::post('course-feedback-questions/update', 'Admin\CourseFeebackController@update');
Route::get('get-lessons-data', ['uses' => 'Admin\LessonsController@getData', 'as' => 'lessons.get_data']);
Route::post('lessons_mass_destroy', ['uses' => 'Admin\LessonsController@massDestroy', 'as' => 'lessons.mass_destroy']);
Route::post('lessons_restore/{id}', ['uses' => 'Admin\LessonsController@restore', 'as' => 'lessons.restore']);
Route::delete('lessons_perma_del/{id}', ['uses' => 'Admin\LessonsController@perma_del', 'as' => 'lessons.perma_del']);

Route::resource('user-feedback-answers', 'Admin\UserFeebackAnswersController');



//===== Questions Routes =====//
Route::resource('questions', 'Admin\QuestionsController');
Route::get('get-questions-data', ['uses' => 'Admin\QuestionsController@getData', 'as' => 'questions.get_data']);
Route::post('questions_mass_destroy', ['uses' => 'Admin\QuestionsController@massDestroy', 'as' => 'questions.mass_destroy']);
Route::post('questions_restore/{id}', ['uses' => 'Admin\QuestionsController@restore', 'as' => 'questions.restore']);
Route::delete('questions_perma_del/{id}', ['uses' => 'Admin\QuestionsController@perma_del', 'as' => 'questions.perma_del']);

Route::any('test_questions/upload_ck_image', 'Admin\TestQuestionController@upload_ck_image')->name('upload_ck_image');

//Route::post('upload_ck_image', 'Admin\TestQuestionController@upload_ck_image')->name('admin.upload_ck_image');
Route::get('test_questions', 'Admin\TestQuestionController@index')->name('test_questions.index');
Route::get('test_questions_delete/{id}', 'Admin\TestQuestionController@test_questions_delete')->name('test_questions_delete');


Route::get('test_questions/create/{course_id?}/{temp_id?}/{onlytest?}', 'Admin\TestQuestionController@create')->name('test_questions.create');
Route::post('test_questions/store', 'Admin\TestQuestionController@store')->name('test_questions.store');
Route::get('test_questions/edit/{id}', 'Admin\TestQuestionController@edit')->name('test_questions.edit');
Route::post('test_questions/update', 'Admin\TestQuestionController@update')->name('test_questions.update');
Route::post('test_questions/question_setup', 'Admin\TestQuestionController@question_setup')->name('test_questions.question_setup');
Route::post('test_questions/question_setup_feedback', 'Admin\TestQuestionController@question_setup_feedback')->name('test_questions.question_setup_feedback');

//===== Questions Options Routes =====//
Route::resource('questions_options', 'Admin\QuestionsOptionsController');
Route::get('get-qo-data', ['uses' => 'Admin\QuestionsOptionsController@getData', 'as' => 'questions_options.get_data']);
Route::post('questions_options_mass_destroy', ['uses' => 'Admin\QuestionsOptionsController@massDestroy', 'as' => 'questions_options.mass_destroy']);
Route::post('questions_options_restore/{id}', ['uses' => 'Admin\QuestionsOptionsController@restore', 'as' => 'questions_options.restore']);
Route::delete('questions_options_perma_del/{id}', ['uses' => 'Admin\QuestionsOptionsController@perma_del', 'as' => 'questions_options.perma_del']);


//===== Tests Routes =====//
Route::resource('tests', 'Admin\TestsController');
Route::get('manual-test', [TestsController::class, 'manualTest']);
Route::post('manual-test', [TestsController::class, 'manualTestStore'])->name("tests.manualTest");
//Route::post('tests/create','Admin\TestsController@store');
Route::get('get-tests-data', ['uses' => 'Admin\TestsController@getData', 'as' => 'tests.get_data']);
Route::post('tests_mass_destroy', ['uses' => 'Admin\TestsController@massDestroy', 'as' => 'tests.mass_destroy']);
Route::post('tests_restore/{id}', ['uses' => 'Admin\TestsController@restore', 'as' => 'tests.restore']);
Route::delete('tests_perma_del/{id}', ['uses' => 'Admin\TestsController@perma_del', 'as' => 'tests.perma_del']);


//===== Media Routes =====//
Route::post('media/remove', ['uses' => 'Admin\MediaController@destroy', 'as' => 'media.destroy']);


//===== User Account Routes =====//
Route::group(['middleware' => ['auth', 'password_expires']], function () {
    Route::get('account', [AccountController::class, 'index'])->name('account');
    Route::patch('account/{email?}', [UserPasswordController::class, 'update'])->name('account.post');
    Route::patch('profile/update', [ProfileController::class, 'update'])->name('profile.update');
});


Route::group(['middleware' => 'role:teacher'], function () {
    //====== Review Routes =====//
    Route::resource('reviews', 'ReviewController');
    Route::get('get-reviews-data', ['uses' => 'ReviewController@getData', 'as' => 'reviews.get_data']);
});



Route::group(['middleware' => 'role:student'], function () {

    
    Route::get('get-certificates', 'CertificateController@getCertificates')->name('certificates.get');

    Route::post('apply-certificate', 'CertificateController@applyCertificate')->name('certificates.apply');
    Route::get('certificates/generate/{course_id}/{user_id}', 'CertificateController@generateCertificate')->name('certificates.generate');
    //==== Certificates ====//
    Route::get('certificates', 'CertificateController@getCertificates')->name('certificates.index');
    // Route::post('certificates/generate', 'CertificateController@generateCertificate')->name('certificates.generate');
    Route::get('certificates/download', ['uses' => 'CertificateController@download', 'as' => 'certificates.download']);

    // view and download certificate
    Route::get('certificates/view-certificate', 'CertificateController@viewCertificate')->name('certificates.view-certificate');
});


//==== Messages Routes =====//
Route::get('messages', ['uses' => 'MessagesController@index', 'as' => 'messages']);
Route::post('messages/unread', ['uses' => 'MessagesController@getUnreadMessages', 'as' => 'messages.unread']);
Route::post('messages/send', ['uses' => 'MessagesController@send', 'as' => 'messages.send']);
Route::post('messages/reply', ['uses' => 'MessagesController@reply', 'as' => 'messages.reply']);


//=== Invoice Routes =====//
Route::get('invoice/download/{order}', ['uses' => 'Admin\InvoiceController@getInvoice', 'as' => 'invoice.download']);
Route::get('invoices/view/{code}', ['uses' => 'Admin\InvoiceController@showInvoice', 'as' => 'invoices.view']);
Route::get('invoices', ['uses' => 'Admin\InvoiceController@getIndex', 'as' => 'invoices.index']);


//======= Blog Routes =====//
Route::group(['prefix' => 'blog'], function () {
    Route::get('/create', 'Admin\BlogController@create');
    Route::post('/create', 'Admin\BlogController@store');
    Route::get('delete/{id}', 'Admin\BlogController@destroy')->name('blogs.delete');
    Route::get('edit/{id}', 'Admin\BlogController@edit')->name('blogs.edit');
    Route::post('edit/{id}', 'Admin\BlogController@update');
    Route::get('view/{id}', 'Admin\BlogController@show');
    //        Route::get('{blog}/restore', 'BlogController@restore')->name('blog.restore');
    Route::post('{id}/storecomment', 'Admin\BlogController@storeComment')->name('storeComment');
});
Route::resource('blogs', 'Admin\BlogController');
Route::get('get-blogs-data', ['uses' => 'Admin\BlogController@getData', 'as' => 'blogs.get_data']);
Route::post('blogs_mass_destroy', ['uses' => 'Admin\BlogController@massDestroy', 'as' => 'blogs.mass_destroy']);


//======= Pages Routes =====//
Route::resource('pages', 'Admin\PageController');
Route::get('get-pages-data', ['uses' => 'Admin\PageController@getData', 'as' => 'pages.get_data']);
Route::post('pages_mass_destroy', ['uses' => 'Admin\PageController@massDestroy', 'as' => 'pages.mass_destroy']);
Route::post('pages_restore/{id}', ['uses' => 'Admin\PageController@restore', 'as' => 'pages.restore']);
Route::delete('pages_perma_del/{id}', ['uses' => 'Admin\PageController@perma_del', 'as' => 'pages.perma_del']);


//==== Reasons Routes ====//
Route::resource('reasons', 'Admin\ReasonController');
Route::get('get-reasons-data', ['uses' => 'Admin\ReasonController@getData', 'as' => 'reasons.get_data']);
Route::post('reasons_mass_destroy', ['uses' => 'Admin\ReasonController@massDestroy', 'as' => 'reasons.mass_destroy']);
Route::get('reasons/status/{id}', 'Admin\ReasonController@status')->name('reasons.status');
Route::post('reasons/status', ['uses' => 'Admin\ReasonController@updateStatus', 'as' => 'reasons.status']);



//==== Live Lessons ====//
Route::group(['prefix' => 'live-lessons'], function () {
    Route::get('data', ['uses' => 'LiveLessonController@getData', 'as' => 'live-lessons.get_data']);
    Route::post('restore/{id}', ['uses' => 'LiveLessonController@restore', 'as' => 'live-lessons.restore']);
    Route::delete('permanent/{id}', ['uses' => 'LiveLessonController@permanent', 'as' => 'live-lessons.perma_del']);
});
Route::resource('live-lessons', 'LiveLessonController');


//==== Live Lessons Slot ====//
Route::group(['prefix' => 'live-lesson-slots'], function () {
    Route::get('data', ['uses' => 'LiveLessonSlotController@getData', 'as' => 'live-lesson-slots.get_data']);
    Route::post('restore/{id}', ['uses' => 'LiveLessonSlotController@restore', 'as' => 'live-lesson-slots.restore']);
    Route::delete('permanent/{id}', ['uses' => 'LiveLessonSlotController@permanent', 'as' => 'live-lesson-slots.perma_del']);
});
Route::resource('live-lesson-slots', 'LiveLessonSlotController');

Route::group(['namespace' => 'Admin\Stripe', 'prefix' => 'stripe', 'as' => 'stripe.'], function () {
    //==== Stripe Plan Controller ====//
    Route::group(['prefix' => 'plans'], function () {
        Route::get('data', ['uses' => 'StripePlanController@getData', 'as' => 'plans.get_data']);
        Route::post('restore/{id}', ['uses' => 'StripePlanController@restore', 'as' => 'plans.restore']);
        Route::delete('permanent/{id}', ['uses' => 'StripePlanController@permanent', 'as' => 'plans.perma_del']);
    });
    Route::resource('plans', 'StripePlanController');
});



// Wishlist Route
Route::get('wishlist/data', ['uses' => 'WishlistController@getData', 'as' => 'wishlist.get_data']);
Route::resource('wishlist', 'WishlistController');

// A.K. Bhakata
Route::get('department', 'Admin\DepartmentController@index')->name('department.index');
Route::get('department-create', 'Admin\DepartmentController@create')->name('department.create');
Route::post('department-store', 'Admin\DepartmentController@store')->name('department.store');
Route::get('department-view/{page}', 'Admin\DepartmentController@show')->name('department.show');
Route::get('department-edit/{page}', 'Admin\DepartmentController@edit')->name('department.edit');
Route::post('department-update/{page}', 'Admin\DepartmentController@update')->name('department.update');
Route::delete('department-destroy/{page}', 'Admin\DepartmentController@destroy')->name('department.destroy');

Route::post('department/import/', 'Admin\DepartmentController@import_exl')->name('department.add.import');

Route::get('get-department-data', ['uses' => 'Admin\DepartmentController@getData', 'as' => 'department.get_data']);
Route::post('department_mass_destroy', ['uses' => 'Admin\DepartmentController@massDestroy', 'as' => 'department.mass_destroy']);
Route::post('department_restore/{page}', ['uses' => 'Admin\DepartmentController@restore', 'as' => 'department.restore']);
Route::delete('department_perma_del/{page}', ['uses' => 'Admin\DepartmentController@perma_del', 'as' => 'department.perma_del']);


// Position
Route::get('position', 'Admin\PositionController@index')->name('position.index');
Route::get('position-create', 'Admin\PositionController@create')->name('position.create');
Route::post('position-store', 'Admin\PositionController@store')->name('position.store');
Route::get('position-view/{page}', 'Admin\PositionController@show')->name('position.show');
Route::get('position-edit/{page}', 'Admin\PositionController@edit')->name('position.edit');
Route::post('position-update/{page}', 'Admin\PositionController@update')->name('position.update');
Route::delete('position-destroy/{page}', 'Admin\PositionController@destroy')->name('position.destroy');

Route::post('position/import/', 'Admin\PositionController@import_exl')->name('position.add.import');

Route::get('get-position-data', ['uses' => 'Admin\PositionController@getData', 'as' => 'position.get_data']);
Route::post('position_mass_destroy', ['uses' => 'Admin\PositionController@massDestroy', 'as' => 'position.mass_destroy']);
Route::post('position_restore/{page}', ['uses' => 'Admin\PositionController@restore', 'as' => 'position.restore']);
Route::delete('position_perma_del/{page}', ['uses' => 'Admin\PositionController@perma_del', 'as' => 'position.perma_del']);

Route::get('subscription', 'Admin\SubscriptionController@index')->name('subscription.index');
Route::get('subscription-create', 'Admin\SubscriptionController@create')->name('subscription.create');
Route::post('subscription-store', 'Admin\SubscriptionController@store')->name('subscription.store');
Route::get('subscription-view/{page}', 'Admin\SubscriptionController@show')->name('subscription.show');
Route::get('subscription-edit/{page}', 'Admin\SubscriptionController@edit')->name('subscription.edit');
Route::post('subscription-update/{page}', 'Admin\SubscriptionController@update')->name('subscription.update');
Route::delete('subscription-destroy/{page}', 'Admin\SubscriptionController@destroy')->name('subscription.destroy');

Route::get('get-subscription-data', ['uses' => 'Admin\SubscriptionController@getData', 'as' => 'subscription.get_data']);
Route::post('subscription_mass_destroy', ['uses' => 'Admin\SubscriptionController@massDestroy', 'as' => 'subscription.mass_destroy']);
Route::post('psubscription_restore/{page}', ['uses' => 'Admin\SubscriptionController@restore', 'as' => 'subscription.restore']);
Route::delete('psubscription_perma_del/{page}', ['uses' => 'Admin\SubscriptionController@perma_del', 'as' => 'subscription.perma_del']);
Route::post('subscription/status', ['uses' => 'Admin\SubscriptionController@updateStatus', 'as' => 'subscription.status']);

// Custom Track Student Progress
Route::get('enrolled-student/{course_id}', 'Admin\EmployeeController@enrolled_student')->name('enrolled_student');
Route::get('course_detail/{course_id}/{employee_id}', 'Admin\CoursesController@course_detail')->name('employee.course_detail');
Route::get('get_data_employee_course/{course_id}/{employee_id}', 'Admin\CoursesController@get_data_employee_course')->name('courses.get_data_employee_course');
Route::get('enrolled_get_data/{course_id}/{show_deleted?}/{search_type?}', 'Admin\EmployeeController@enrolled_get_data')->name('employee.enrolled_get_data');
Route::get('all-enrolled-student/{course_id}', 'Admin\EmployeeController@enrolled_student')->name('all-enrolled-student');
Route::get('internal_reports/{course_id}', 'Admin\EmployeeController@internal_reports')->name('employee.internal_reports');
Route::get('enrolled_get_data_internal/{id?}', 'Admin\EmployeeController@enrolled_get_data_internal')->name('employee.enrolled_get_data_internal');
Route::get('reports_create_internal', 'Admin\EmployeeController@reports_create_internal')->name('employee.reports_create_internal');
Route::post('reports_store_internal', 'Admin\EmployeeController@reports_store_internal')->name('employee.reports_store_internal');

Route::post('/admin/sync-report', 'Admin\EmployeeController@sync_report')->name('sync.report');
// user informations

Route::get('internal_trainee_info', 'Admin\EmployeeController@internal_trainee_info')->name('employee.internal_trainee_info');
Route::get('external_trainee_info', 'Admin\EmployeeController@external_trainee_info')->name('employee.external_trainee_info');
Route::get('get_external_trainee_info', 'Admin\EmployeeController@get_external_trainee_info')->name('employee.get_external_trainee_info');

// end user information

// user attendence report

Route::get('internal-attendence-report', 'Admin\EmployeeController@internal_attendence_report')->name('employee.internal-attendence-report');
Route::get('assessement-answers/{user_id}/{course_id}', 'Admin\EmployeeController@view_user_asssessement_answers')->name('employee.assessement-answers');
Route::get('export-internal-attendence-report-as-csv', 'Admin\EmployeeController@exportInternalAttendenceReportAsCsv')->name('employee.internal-progress-report');
Route::get('export-trainees-as-csv', 'Admin\EmployeeController@exportTraineesAsCsv');
Route::get('external-attendence-report', 'Admin\EmployeeController@external_attendence_report')->name('employee.external-attendence-report');


Route::get('course-assignment-report-as-csv', 'Admin\CoursesController@exportCourseAssignmentReportAsCsv');

// user attendence report

//=== Attendence Routes =====//
Route::get('attendence', ['uses' => 'Admin\AttendenceController@getIndex', 'as' => 'attendence.index']);
Route::post('mark-attendence', ['uses' => 'Admin\AttendenceController@setAttendence', 'as' => 'attendence.mark_attendence']);


// News Routes
Route::get('news', 'Admin\NewsController@index')->name('news.index');
Route::get('news-create', 'Admin\NewsController@create')->name('news.create');
Route::post('news-store', 'Admin\NewsController@store')->name('news.store');
Route::get('news-view/{page}', 'Admin\NewsController@show')->name('news.show');
Route::get('news-edit/{page}', 'Admin\NewsController@edit')->name('news.edit');
Route::post('news-update/{page}', 'Admin\NewsController@update')->name('news.update');
Route::get('get-news-data', ['uses' => 'Admin\NewsController@getData', 'as' => 'news.get_data']);
Route::delete('news_mass_destroy/{page}', ['uses' => 'Admin\NewsController@destroy', 'as' => 'news.destroy']);
Route::get('news/status/{id}', 'Admin\NewsController@status')->name('news.status');
Route::post('news/status', ['uses' => 'Admin\NewsController@updateStatus', 'as' => 'news.status']);

// Latest Events
Route::get('events', 'Admin\EventsController@index')->name('events.index');
Route::get('events-create', 'Admin\EventsController@create')->name('events.create');
Route::post('events-store', 'Admin\EventsController@store')->name('events.store');
Route::get('events-view/{page}', 'Admin\EventsController@show')->name('events.show');
Route::get('events-edit/{page}', 'Admin\EventsController@edit')->name('events.edit');
Route::post('events-update/{page}', 'Admin\EventsController@update')->name('events.update');
Route::get('get-events-data', ['uses' => 'Admin\EventsController@getData', 'as' => 'events.get_data']);
Route::delete('events_mass_destroy/{page}', ['uses' => 'Admin\EventsController@destroy', 'as' => 'events.destroy']);
Route::get('events/status/{id}', 'Admin\EventsController@status')->name('events.status');
Route::post('events/status', ['uses' => 'Admin\EventsController@updateStatus', 'as' => 'events.status']);

// Latest Libraries
Route::get('libraries', 'Admin\LibraryController@index')->name('libraries.index');
Route::get('libraries-create', 'Admin\LibraryController@create')->name('libraries.create');
Route::post('libraries-store', 'Admin\LibraryController@store')->name('libraries.store');
Route::get('libraries-view/{page}', 'Admin\LibraryController@show')->name('libraries.show');
Route::get('libraries-edit/{page}', 'Admin\LibraryController@edit')->name('libraries.edit');
Route::post('libraries-update/{page}', 'Admin\LibraryController@update')->name('libraries.update');
Route::get('get-libraries-data', ['uses' => 'Admin\LibraryController@getData', 'as' => 'libraries.get_data']);
Route::delete('libraries_mass_destroy/{page}', ['uses' => 'Admin\LibraryController@destroy', 'as' => 'libraries.destroy']);
Route::get('libraries/status/{id}', 'Admin\LibraryController@status')->name('libraries.status');
Route::post('libraries/status', ['uses' => 'Admin\LibraryController@updateStatus', 'as' => 'libraries.status']);


// Announcement
Route::get('announcement', 'Admin\AnnouncementController@index')->name('announcement.index');
Route::get('announcement-create', 'Admin\AnnouncementController@create')->name('announcement.create');
Route::post('announcement-store', 'Admin\AnnouncementController@store')->name('announcement.store');
Route::get('announcement-view/{page}', 'Admin\AnnouncementController@show')->name('announcement.show');
Route::get('announcement-edit/{page}', 'Admin\AnnouncementController@edit')->name('announcement.edit');
Route::post('announcement-update/{page}', 'Admin\AnnouncementController@update')->name('announcement.update');
Route::get('get-announcement-data', ['uses' => 'Admin\AnnouncementController@getData', 'as' => 'announcement.get_data']);
Route::delete('announcement_mass_destroy/{page}', ['uses' => 'Admin\AnnouncementController@destroy', 'as' => 'announcement.destroy']);
Route::get('announcement/status/{id}', 'Admin\AnnouncementController@status')->name('announcement.status');
Route::post('announcement/status', ['uses' => 'Admin\AnnouncementController@updateStatus', 'as' => 'announcement.status']);

// Student Feedback
Route::get('student-feedback', 'Admin\StudentFeedbackController@index')->name('student.feedback.index');
Route::get('student-feedback-create', 'Admin\StudentFeedbackController@create')->name('student.feedback.create');
Route::post('student-feedback-store', 'Admin\StudentFeedbackController@store')->name('student.feedback.store');
Route::get('student-feedback-view/{page}', 'Admin\StudentFeedbackController@show')->name('student.feedback.show');
Route::get('student-feedback-edit/{page}', 'Admin\StudentFeedbackController@edit')->name('student.feedback.edit');
Route::post('student-feedback-update/{page}', 'Admin\StudentFeedbackController@update')->name('student.feedback.update');
Route::get('get-student-feedback-data', ['uses' => 'Admin\StudentFeedbackController@getData', 'as' => 'student.feedback.get_data']);
Route::delete('student_feedback_mass_destroy/{page}', ['uses' => 'Admin\StudentFeedbackController@destroy', 'as' => 'student.feedback.destroy']);
Route::get('student-feedback/status/{id}', 'Admin\StudentFeedbackController@status')->name('student.feedback.status');
Route::post('student-feedback/status', ['uses' => 'Admin\StudentFeedbackController@updateStatus', 'as' => 'student.feedback.status']);

//Feedback
Route::get('feedback-questions/{id?}', 'Admin\FeedbackController@index')->name('feedback_question.index');
Route::get('feedback-question/edit/{id}', 'Admin\FeedbackController@edit')->name('feedback_question.edit');
Route::post('feedback-question/update/{id}', 'Admin\FeedbackController@feedbackQuestionUpdate')->name('feedback_question.update');


Route::get('feedback-questions-get-data', 'Admin\FeedbackController@getData')->name('feedback_question.get_data');
Route::get('feedback-question-create', 'Admin\FeedbackController@createQuestion')->name('feedback.create_question');
Route::get('course-feedback-create', 'Admin\FeedbackController@createCourseFeedback')->name('feedback.create_course_feedback');
Route::get('feedback-form-create/{id}', 'Admin\FeedbackController@createFeedbackForm')->name('feedback.create_feedback_form');

Route::post('feedback-question-store', 'Admin\FeedbackController@store')->name('feedback.store');
Route::post('course-feedback-store', 'Admin\FeedbackController@storeCourseFeedback')->name('feedback.course_feedback_store');
Route::post('user-responses-store', 'Admin\FeedbackController@storeUserResponses')->name('feedback.store_user_responses');

Route::delete('feedback/{id}', ['uses' => 'Admin\FeedbackController@destroy', 'as' => 'feedback.destroy']);

Route::get('get-feedback-detail/{id}', 'Admin\UserFeebackAnswersController@feedback_detail')->name('feedback.detail');

// feebback with multiple choice questions
Route::get('feedback-question-multiple/{id?}', 'Admin\FeedbackController@feedback_questions')->name('feedback.feedback-question-multiple');
Route::post('feedback-question-multiple-store', 'Admin\FeedbackController@feedback_questions_store')->name('feedback.feedback-question-multiple-store');
Route::post('feedback-question-multiple-delete', 'Admin\FeedbackController@feedback_questions_delete')->name('feedback.feedback-question-multiple-delete');


Route::get('send-email-notification', 'Admin\EmailNotificationController@sendEmailNotification');
Route::post('send-email-notification', 'Admin\EmailNotificationController@sendEmailNotificationPost');


Route::get('check-export-download-ready', 'Admin\EmployeeController@checkExportDownloadReady');