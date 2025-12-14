<?php

use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\DepartmentController;
use App\Http\Controllers\Backend\SubscriptionController;
use App\Http\Controllers\Backend\AssessmentController;
use App\Http\Controllers\Frontend\Auth\RegisterController;
use App\Http\Controllers\CalenderController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\UserCourseRequestController;
use App\Jobs\SendEmailJob;
use App\Models\AssignmentQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

//Route::get('/install', [InstallerController::class, 'index']);
//Route::post('/install/run', [InstallerController::class, 'run']);

Route::get('syncCourseAssignmentAndSubscribeCourseData', function () {
    CustomHelper::syncCourseAssignmentAndSubscribeCourseData();
});

Route::get('complete-course/{course_id}/{user_id}', function (Request $request) {
    CustomHelper::completeCourseForUser($request->course_id, $request->user_id);
});
// Route::get('loginById/{user_id}', function (Request $request) {
//     Auth::loginUsingId($request->user_id);
// });

Route::get('email-test', function () {
    $details['email'] = 'anupdeveloper07@gmail.com';
    $details['to'] = 'anupdeveloper07@gmail.com';
    $details['subject'] = 'Please subscribe to this course';
    $details['name'] = 'Anup Bhakta';
    $details['message'] = 'Please subscribe to this course';
    dispatch(new SendEmailJob($details));
    dd('done');
});

// Switch between the included languages
Route::get('lang/{lang}', [LanguageController::class, 'swap']);


Route::get('/sitemap-' . Str::slug(config('app.name')) . '/{file?}', 'SitemapController@index');


//============ Remove this  while creating zip for Envato ===========//

/*This command is useful in demo site you can go to https://demo.neonlms.com/reset-demo and it will refresh site from this URL. */

Route::get('reset-demo', function () {
    ini_set('memory_limit', '-1');
    ini_set('max_execution_time', 1000);
    try {
        \Illuminate\Support\Facades\Artisan::call('refresh:site');
        return 'Refresh successful!';
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});
//===================================================================//


require_once "delta_academy_custom_routes.php";


/*
 * Frontend Routes
 * Namespaces indicate folder structure
 */
Route::group(['namespace' => 'Frontend', 'as' => 'frontend.'], function () {
    include __DIR__ . '/frontend/auth.php';
});

/*
 * Backend Routes
 * Namespaces indicate folder structure
 */
Route::group(['namespace' => 'Backend', 'prefix' => 'user', 'as' => 'admin.', 'middleware' => 'admin'], function () {
    
    
    include __DIR__ . '/backend/admin.php';
    include __DIR__ . '/backend/auth.php';
});

Route::group(['namespace' => 'Backend', 'prefix' => 'user', 'as' => 'admin.', 'middleware' => 'auth'], function () {

    //==== Messages Routes =====//
    Route::get('messages', ['uses' => 'MessagesController@index', 'as' => 'messages']);
    Route::post('messages/unread', ['uses' => 'MessagesController@getUnreadMessages', 'as' => 'messages.unread']);
    Route::post('messages/send', ['uses' => 'MessagesController@send', 'as' => 'messages.send']);
    Route::post('messages/reply', ['uses' => 'MessagesController@reply', 'as' => 'messages.reply']);
});


Route::get('category/{category}/blogs', 'BlogController@getByCategory')->name('blogs.category');
Route::get('tag/{tag}/blogs', 'BlogController@getByTag')->name('blogs.tag');
Route::get('blog/{slug?}', 'BlogController@getIndex')->name('blogs.index');
Route::post('blog/{id}/comment', 'BlogController@storeComment')->name('blogs.comment');
Route::get('blog/comment/delete/{id}', 'BlogController@deleteComment')->name('blogs.comment.delete');

Route::get('teachers', 'Frontend\HomeController@getTeachers')->name('teachers.index');
Route::get('teachers/{id}/show', 'Frontend\HomeController@showTeacher')->name('teachers.show');




Route::post('newsletter/subscribe', 'Frontend\HomeController@subscribe')->name('subscribe');


/*=============== Department Routes ===========================*/
Route::get('departments', [DepartmentController::class, 'index'])->name('departments.list');

Route::get('verify/{token}', [RegisterController::class, 'verifyUser']);


Route::get('department/{id}', [DepartmentController::class, 'show'])->name('department.show');
/*=============== End Department routes =======================*/

//============Course Routes=================//
Route::get('courses', ['uses' => 'CoursesController@all', 'as' => 'courses.all']);
Route::get('cme-courses', ['uses' => 'CoursesController@allCme', 'as' => 'courses.allCme']);
Route::get('course/{slug}', ['uses' => 'CoursesController@show', 'as' => 'courses.show'])->middleware(['subscribed', 'auth']);
Route::get('course-preview/{slug}', 'CoursesController@coursePreview')->name('coursePreview')->middleware('subscribed');

//Route::post('course/payment', ['uses' => 'CoursesController@payment', 'as' => 'courses.payment']);
Route::post('course/{course_id}/rating', ['uses' => 'CoursesController@rating', 'as' => 'courses.rating']);
Route::get('category/{category}/courses', ['uses' => 'CoursesController@getByCategory', 'as' => 'courses.category']);
Route::post('courses/{id}/review', ['uses' => 'CoursesController@addReview', 'as' => 'courses.review']);
Route::get('courses/review/{id}/edit', ['uses' => 'CoursesController@editReview', 'as' => 'courses.review.edit']);
Route::post('courses/review/{id}/edit', ['uses' => 'CoursesController@updateReview', 'as' => 'courses.review.update']);
Route::get('courses/review/{id}/delete', ['uses' => 'CoursesController@deleteReview', 'as' => 'courses.review.delete']);


//============Bundle Routes=================//
Route::get('bundles', ['uses' => 'BundlesController@all', 'as' => 'bundles.all']);
Route::get('bundle/{slug}', ['uses' => 'BundlesController@show', 'as' => 'bundles.show']);
//Route::post('course/payment', ['uses' => 'CoursesController@payment', 'as' => 'courses.payment']);
Route::post('bundle/{bundle_id}/rating', ['uses' => 'BundlesController@rating', 'as' => 'bundles.rating']);
Route::get('category/{category}/bundles', ['uses' => 'BundlesController@getByCategory', 'as' => 'bundles.category']);
Route::post('bundles/{id}/review', ['uses' => 'BundlesController@addReview', 'as' => 'bundles.review']);
Route::get('bundles/review/{id}/edit', ['uses' => 'BundlesController@editReview', 'as' => 'bundles.review.edit']);
Route::post('bundles/review/{id}/edit', ['uses' => 'BundlesController@updateReview', 'as' => 'bundles.review.update']);
Route::get('bundles/review/{id}/delete', ['uses' => 'BundlesController@deleteReview', 'as' => 'bundles.review.delete']);


Route::group(['middleware' => 'auth'], function () {
    Route::get('lesson/{course_id}/{slug?}/', ['uses' => 'LessonsController@show', 'as' => 'lessons.show']);
    Route::post('lesson/{slug}/test', ['uses' => 'LessonsController@test', 'as' => 'lessons.test']);
    Route::post('lesson/{slug}/retest', ['uses' => 'LessonsController@retest', 'as' => 'lessons.retest']);
    Route::post('video/progress', 'LessonsController@videoProgress')->name('update.videos.progress');
    Route::post('lesson/progress', 'LessonsController@courseProgress')->name('update.course.progress');
    Route::post('video/progress/update', 'LessonsController@videoProgressUpdates')->name('video.progress.update');
    Route::post('lesson/book-slot', 'LessonsController@bookSlot')->name('lessons.course.book-slot');
    Route::get('lesson/check-course', 'Backend\Admin\LessonsController@create')->name('lessons.course.check');
    Route::get('/record-attendance/{slug}', 'Backend\Admin\CoursesController@recordAttendance')->name('recordAttendance');
});

Route::get('/search', [HomeController::class, 'searchCourse'])->name('search');
Route::get('/search-course', [HomeController::class, 'searchCourse'])->name('search-course');
Route::get('/search-bundle', [HomeController::class, 'searchBundle'])->name('search-bundle');
Route::get('/search-blog', [HomeController::class, 'searchBlog'])->name('blogs.search');


Route::get('/faqs', 'Frontend\HomeController@getFaqs')->name('faqs');


// Assignment Part(Manish Giri)
Route::get('online_assessment', 'Frontend\AssessmentController@index')->name('online_assessment');
Route::get('manual_online_assessment', 'Frontend\AssessmentController@manualOnlineAssessment')->name('manual_online_assessment');
Route::get('course-feedback/{course_id}', 'Frontend\AssessmentController@courseFeedback')->name('course-feedback');
Route::post('online_assessment/verify', 'Frontend\AssessmentController@verify')->name('online_assessment.verify');
Route::get('online_assessment/verifyDetails', 'Frontend\AssessmentController@verifyDetails')->name('online_assessment.verifyDetails');
Route::post('online_assessment/store_user_details', 'Frontend\AssessmentController@store_user_details')->name('online_assessment.store_user_details');
Route::get('online_assessment/question_set', 'Frontend\AssessmentController@question_set')->name('online_assessment.question_set');
Route::post('online_assessment/answer_submit', 'Frontend\AssessmentController@answer_submit')->name('online_assessment.answer_submit');

Route::post('online_assessment/feedback_submit', 'Frontend\AssessmentController@feedback_submit')->name('online_assessment.feedback_submit');

Route::post('online_assessment/assignment_test_elapsed_time', 'Frontend\AssessmentController@assignment_test_elapsed_time')->name('online_assessment.assignment_test_elapsed_time');
Route::post('online_assessment/assignment_test_report', 'Frontend\AssessmentController@assignment_test_report')->name('online_assessment.assignment_test_report');
/*=============== Theme blades routes ends ===================*/

Route::get('contact', 'Frontend\ContactController@index')->name('contact');
Route::post('contact/send', 'Frontend\ContactController@send')->name('contact.send');

Route::get('request-course/{slug?}', [UserCourseRequestController::class, 'requestCourse']);
Route::post('request-course-submit', [UserCourseRequestController::class, 'requestCourseSubmit']);


Route::get('download', ['uses' => 'Frontend\HomeController@getDownload', 'as' => 'download']);

Route::group(['middleware' => 'auth'], function () {
    Route::post('cart/checkout', ['uses' => 'CartController@checkout', 'as' => 'cart.checkout']);
    Route::post('cart/add', ['uses' => 'CartController@addToCart', 'as' => 'cart.addToCart']);
    Route::get('cart', ['uses' => 'CartController@index', 'as' => 'cart.index']);
    Route::get('cart/clear', ['uses' => 'CartController@clear', 'as' => 'cart.clear']);
    Route::get('cart/remove', ['uses' => 'CartController@remove', 'as' => 'cart.remove']);
    Route::post('cart/apply-coupon', ['uses' => 'CartController@applyCoupon', 'as' => 'cart.applyCoupon']);
    Route::post('cart/remove-coupon', ['uses' => 'CartController@removeCoupon', 'as' => 'cart.removeCoupon']);
    Route::post('cart/stripe-payment', ['uses' => 'CartController@stripePayment', 'as' => 'cart.stripe.payment']);
    Route::post('cart/paypal-payment', ['uses' => 'CartController@paypalPayment', 'as' => 'cart.paypal.payment']);
    Route::get('cart/paypal-payment/status', ['uses' => 'CartController@getPaymentStatus'])->name('cart.paypal.status');

    Route::post('cart/instamojo-payment', ['uses' => 'CartController@instamojoPayment', 'as' => 'cart.instamojo.payment']);
    Route::get('cart/instamojo-payment/status', ['uses' => 'CartController@getInstamojoStatus'])->name('cart.instamojo.status');

    Route::post('cart/razorpay-payment', ['uses' => 'CartController@razorpayPayment', 'as' => 'cart.razorpay.payment']);
    Route::post('cart/razorpay-payment/status', ['uses' => 'CartController@getRazorpayStatus'])->name('cart.razorpay.status');

    Route::post('cart/cashfree-payment', ['uses' => 'CartController@cashfreeFreePayment', 'as' => 'cart.cashfree.payment']);
    Route::post('cart/cashfree-payment/status', ['uses' => 'CartController@getCashFreeStatus'])->name('cart.cashfree.status');

    Route::post('cart/payu-payment', ['uses' => 'CartController@payuPayment', 'as' => 'cart.payu.payment']);
    Route::post('cart/payu-payment/status', ['uses' => 'CartController@getPayUStatus'])->name('cart.pauy.status');

    Route::match(['GET', 'POST'], 'cart/flutter-payment', ['uses' => 'CartController@flatterPayment', 'as' => 'cart.flutter.payment']);
    Route::get('cart/flutter-payment/status', ['uses' => 'CartController@getFlatterStatus', 'as' => 'cart.flutter.status']);

    Route::get('status', function () {
        return view('frontend.cart.status');
    })->name('status');
    Route::post('cart/offline-payment', ['uses' => 'CartController@offlinePayment', 'as' => 'cart.offline.payment']);
    Route::post('cart/getnow', ['uses' => 'CartController@getNow', 'as' => 'cart.getnow']);
});

//============= Menu  Manager Routes ===============//
Route::group(['namespace' => 'Backend', 'prefix' => 'admin', 'middleware' => config('menu.middleware')], function () {
    //Route::get('wmenuindex', array('uses'=>'\Harimayco\Menu\Controllers\MenuController@wmenuindex'));
    Route::post('add-custom-menu', 'MenuController@addcustommenu')->name('haddcustommenu');
    Route::post('delete-item-menu', 'MenuController@deleteitemmenu')->name('hdeleteitemmenu');
    Route::post('delete-menug', 'MenuController@deletemenug')->name('hdeletemenug');
    Route::post('create-new-menu', 'MenuController@createnewmenu')->name('hcreatenewmenu');
    Route::post('generate-menu-control', 'MenuController@generatemenucontrol')->name('hgeneratemenucontrol');
    Route::post('update-item', 'MenuController@updateitem')->name('hupdateitem');
    Route::post('save-custom-menu', 'MenuController@saveCustomMenu')->name('hcustomitem');
    Route::post('change-location', 'MenuController@updateLocation')->name('update-location');
});

Route::get('certificate-verification', 'Backend\CertificateController@getVerificationForm')->name('frontend.certificates.getVerificationForm');
Route::post('certificate-verification', 'Backend\CertificateController@verifyCertificate')->name('frontend.certificates.verify');
Route::get('certificates/download', ['uses' => 'Backend\CertificateController@download', 'as' => 'certificates.download']);
Route::get('user/certificates/generate/{course_id}/{user_id}', 'Backend\CertificateController@generateCertificate')->name('admin.certificates.generate');


if (config('show_offers') == 1) {
    Route::get('offers', ['uses' => 'CartController@getOffers', 'as' => 'frontend.offers']);
}

Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'auth', 'role:teacher|administrator']], function () {
    \UniSharp\LaravelFilemanager\Lfm::routes();
});

Route::group(['prefix' => 'subscription'], function () {
    Route::get('plans', 'SubscriptionController@plans')->name('subscription.plans');
    Route::get('/{plan}/{name}', 'SubscriptionController@showForm')->name('subscription.form');
    Route::post('subscribe/{plan}', 'SubscriptionController@subscribe')->name('subscription.subscribe');
    Route::post('update/{plan}', 'SubscriptionController@updateSubscription')->name('subscription.update');
    Route::get('status', 'SubscriptionController@status')->name('subscription.status');
    Route::post('subscribe', 'SubscriptionController@courseSubscribed')->name('subscription.course_subscribe');
});

Route::get('subscriptions', [SubscriptionController::class, 'show_list'])->name('user.subscriptions');
Route::get('get-subscription-data', [SubscriptionController::class, 'getData'])->name('user.subscriptions.getdata');

// wishlist
Route::post('add-to-wishlist', 'Backend\WishlistController@store')->name('add-to-wishlist');

Route::group(['namespace' => 'Frontend', 'as' => 'frontend.'], function () {
    Route::get('/{page?}', [HomeController::class, 'index'])->name('index');
});


Route::post('add-to-subscription', 'SubscriptionController@add_subscription')->name('add.subscription');
Route::group(['middleware' => 'auth', 'prefix' => 'user'], function () {
    Route::get('calender', [CalenderController::class, 'show_list'])->name('user.calender');
    Route::post('add-event', [CalenderController::class, 'add_event'])->name('user.add-event');
    Route::get('myassignment', [AssessmentController::class, 'myassignment'])->name('user.myassignment');
    Route::get('myassignment/data', [AssessmentController::class, 'getMyAssignmentData'])->name('user.myassignment.getdata');

    Route::get('my-courses', [CoursesController::class, 'mycourses'])->name('user.mycourses');
    Route::get('my-pathway-courses', [CoursesController::class, 'mypathwaycourses'])->name('user.mypathwaycourses');
});

Route::get('my-courses/data', [CoursesController::class, 'getMyCoursesData'])->name('user.mycourses.getdata');
Route::get('my-pathwaycourses/data', [CoursesController::class, 'getMyPathWayCoursesData'])->name('user.mypathwaycourses.getdata');
