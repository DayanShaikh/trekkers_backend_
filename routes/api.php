<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\HelperController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\HeaderVideoController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\FrontMenuController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\AgeGroupController;
use App\Http\Controllers\Admin\TripTypeController;
use App\Http\Controllers\Admin\DestinationController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\LocationPickupController;
use App\Http\Controllers\Admin\LocationAddonController;
use App\Http\Controllers\Admin\TripController;
use App\Http\Controllers\Admin\TripTicketController;
use App\Http\Controllers\Admin\TripTicketUserController;
use App\Http\Controllers\Admin\TripTourGuideController;
use App\Http\Controllers\Admin\TripDocumentController;
use App\Http\Controllers\Admin\TripTempleteController;
use App\Http\Controllers\Admin\TourGuideInfoController;
use App\Http\Controllers\Admin\ReminderController;
use App\Http\Controllers\Admin\TravelAgentController;
use App\Http\Controllers\Admin\TravelAdminController;
use App\Http\Controllers\Admin\TravelBrandController;
use App\Http\Controllers\Admin\TripBookingController;
use App\Http\Controllers\Admin\TripBookingNoteController;
use App\Http\Controllers\Admin\TripBookingDocumentController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\AirlineController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\ReservationNoteController;
use App\Http\Controllers\Admin\TripBookingAddonController;
use App\Http\Controllers\Admin\TripBookingPaymentController;
use App\Http\Controllers\Admin\TripBookingExtraInsuranceController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\SupportCategoryController;
use App\Http\Controllers\Admin\SupportArticleController;
use App\Http\Controllers\Admin\ForumCategoryController;
use App\Http\Controllers\Admin\ForumTopicController;
use App\Http\Controllers\Admin\ForumReplyController;
use App\Http\Controllers\Admin\ConfigPageController;
use App\Http\Controllers\Admin\ConfigVariableController;
use App\Http\Controllers\Admin\TripTemplateController;
use App\Http\Controllers\Admin\UploadController;
use App\Http\Controllers\Admin\LocationDayController;
use App\Http\Controllers\Admin\TripGroupController;
use App\Http\Controllers\Admin\PageGalleryController;
use App\Http\Controllers\Admin\PageCountryController;
use App\Http\Controllers\Admin\PageRedirectController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\QuizQuestionController;
use App\Http\Controllers\Admin\QuizQuestionAnswerController;
use App\Http\Controllers\Admin\LandingPageController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\EmailTemplateConditionController;
use App\Http\Controllers\Admin\UserNoteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TourGuideController;
use App\Http\Controllers\Admin\ReportController;
use App\Models\User;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', [LoginController::class, 'authenticate'])->middleware('guest');

Route::post('password/email', [ForgotPasswordController::class,'sendResetLinkEmail'])->name('password.email');
//Route::post('reset-password', [ForgotPasswordController::class,'resetPassword'])->middleware('guest')->name('password.reset');

// password reset
Route::post('forgot-password', [ResetPasswordController::class, 'sendResetToken'])->middleware('guest')->name('password.email');
Route::post('verity-reset-token', [ResetPasswordController::class, 'verifyToken'])->middleware('guest')->name('password.email');
Route::post('reset-password', [ResetPasswordController::class, 'reset'])->middleware('guest')->name('password.reset');

Route::middleware(['auth:sanctum'])->group(function () {

	//logout
	Route::post('logout', [LoginController::class, 'logout']);
	Route::post('get-current-user', [UserController::class, 'getAuthUser']);

	//Users
	Route::get('get_tour_guide_user', [UserController::class, 'getTourGuideUser']);
	Route::get('users/restore/{id}', [UserController::class, 'restore']);
	Route::get('users/force-delete/{id}', [UserController::class, 'forceDelete']);
	Route::patch('users/delete-multiple', [UserController::class, 'massDestroy']);
	Route::patch('users/restore-multiple', [UserController::class, 'massRestore']);
	Route::patch('users/force-delete-multiple', [UserController::class, 'massForceDelete']);
	Route::apiResource('users', UserController::class);
	Route::put('users/{user}/update-active-status', [UserController::class, 'updateActiveStatus']);

	//Roles
	Route::get('roles/restore/{id}', [RoleController::class, 'restore']);
	Route::get('roles/force-delete/{id}', [RoleController::class, 'forceDelete']);
	Route::patch('roles/restore-multiple', [RoleController::class, 'massRestore']);
	Route::patch('roles/force-delete-multiple', [RoleController::class, 'massForceDelete']);
	Route::patch('roles/delete-multiple', [RoleController::class, 'massDestroy']);
	Route::patch('roles/restore-multiple', [RoleController::class, 'massRestore']);
	Route::apiResource('roles', RoleController::class);
	Route::put('roles/{role}/update-active-status', [RoleController::class, 'updateActiveStatus']);

	//Discounts
	Route::get('discounts/restore/{id}', [DiscountController::class, 'restore']);
	Route::get('discounts/force-delete/{id}', [DiscountController::class, 'forceDelete']);
	Route::patch('discounts/restore-multiple', [DiscountController::class, 'massRestore']);
	Route::patch('discounts/force-delete-multiple', [DiscountController::class, 'massForceDelete']);
	Route::patch('discounts/delete-multiple', [DiscountController::class, 'massDestroy']);
	Route::apiResource('discounts', DiscountController::class);
	Route::put('discounts/{discount}/update-active-status', [DiscountController::class, 'updateActiveStatus']);

	//Header Videos
	Route::get('header_videos/restore/{id}', [HeaderVideoController::class, 'restore']);
	Route::get('header_videos/force-delete/{id}', [HeaderVideoController::class, 'forceDelete']);
	Route::patch('header_videos/restore-multiple', [HeaderVideoController::class, 'massRestore']);
	Route::patch('header_videos/force-delete-multiple', [HeaderVideoController::class, 'massForceDelete']);
	Route::patch('header_videos/delete-multiple', [HeaderVideoController::class, 'massDestroy']);
	Route::apiResource('header_videos', HeaderVideoController::class);
	Route::put('header_videos/{headerVideo}/update-active-status', [HeaderVideoController::class, 'updateActiveStatus']);

	//Attributes
	Route::get('attributes/restore/{id}', [AttributeController::class, 'restore']);
	Route::get('attributes/force-delete/{id}', [AttributeController::class, 'forceDelete']);
	Route::patch('attributes/restore-multiple', [AttributeController::class, 'massRestore']);
	Route::patch('attributes/force-delete-multiple', [AttributeController::class, 'massForceDelete']);
	Route::patch('attributes/delete-multiple', [AttributeController::class, 'massDestroy']);
	Route::apiResource('attributes', AttributeController::class);
	Route::put('attributes/{attribute}/update-active-status', [AttributeController::class, 'updateActiveStatus']);

	//Front Menus
	Route::get('front_menus_parent', [FrontMenuController::class, 'frontMenuParent']);
	Route::get('front_menus/restore/{id}', [FrontMenuController::class, 'restore']);
	Route::get('front_menus/force-delete/{id}', [FrontMenuController::class, 'forceDelete']);
	Route::patch('front_menus/restore-multiple', [FrontMenuController::class, 'massRestore']);
	Route::patch('front_menus/force-delete-multiple', [FrontMenuController::class, 'massForceDelete']);
	Route::patch('front_menus/delete-multiple', [FrontMenuController::class, 'massDestroy']);
	Route::apiResource('front_menus', FrontMenuController::class);
	Route::put('front_menus/{frontMenu}/update-active-status', [FrontMenuController::class, 'updateActiveStatus']);
	Route::put('front_menus/{frontMenu}/update-active-default', [FrontMenuController::class, 'updateActiveDefault']);

	Route::get('pages/restore/{id}', [PageController::class, 'restore']);
	Route::get('pages/force-delete/{id}', [PageController::class, 'forceDelete']);
	Route::patch('pages/restore-multiple', [PageController::class, 'massRestore']);
	Route::patch('pages/force-delete-multiple', [PageController::class, 'massForceDelete']);
	Route::patch('pages/delete-multiple', [PageController::class, 'massDestroy']);
	Route::apiResource('pages', PageController::class);
	Route::put('pages/{page}/update-active-status', [PageController::class, 'updateActiveStatus']);
	Route::get('page_model', [PageController::class, 'getPageModel']);
	// Route::apiResource('update_page_detail', PageController::class);

	//Page Gallery
	Route::get('page_galleries/restore/{id}', [PageGalleryController::class, 'restore']);
	Route::get('page_galleries/force-delete/{id}', [PageGalleryController::class, 'forceDelete']);
	Route::patch('page_galleries/restore-multiple', [PageGalleryController::class, 'massRestore']);
	Route::patch('page_galleries/force-delete-multiple', [PageGalleryController::class, 'massForceDelete']);
	Route::patch('page_galleries/delete-multiple', [PageGalleryController::class, 'massDestroy']);
	Route::apiResource('page_galleries', PageGalleryController::class);
	Route::put('page_galleries/{pageGallery}/update-active-status', [PageGalleryController::class, 'updateActiveStatus']);
	Route::get('page_gallery/{page_id}', [PageGalleryController::class, 'GetDataByPageId']);

	//Page Country
	//Route::apiResource('page_countries', PageCountryController::class);
	//Route::put('page_countries/{pageGallery}/update-active-status', [PageCountryController::class, 'updateActiveStatus']);
	//Route::get('page_countries/{page_id}', [PageCountryController::class, 'GetDataByPageId']);
	//Route::get('page/page_countries/{page_id}', [PageCountryController::class, 'getPageCountry']);

	//Age Group
	Route::get('age_groups/restore/{id}', [AgeGroupController::class, 'restore']);
	Route::get('age_groups/force-delete/{id}', [AgeGroupController::class, 'forceDelete']);
	Route::patch('age_groups/restore-multiple', [AgeGroupController::class, 'massRestore']);
	Route::patch('age_groups/force-delete-multiple', [AgeGroupController::class, 'massForceDelete']);
	Route::patch('age_groups/delete-multiple', [AgeGroupController::class, 'massDestroy']);
	Route::apiResource('age_groups', AgeGroupController::class);
	Route::put('age_groups/{ageGroup}/update-active-status', [AgeGroupController::class, 'updateActiveStatus']);

	//Trip Type
	Route::get('trip_types/restore/{id}', [TripTypeController::class, 'restore']);
	Route::get('trip_types/force-delete/{id}', [TripTypeController::class, 'forceDelete']);
	Route::patch('trip_types/restore-multiple', [TripTypeController::class, 'massRestore']);
	Route::patch('trip_types/force-delete-multiple', [TripTypeController::class, 'massForceDelete']);
	Route::patch('trip_types/delete-multiple', [TripTypeController::class, 'massDestroy']);
	Route::apiResource('trip_types', TripTypeController::class);
	Route::put('trip_types/{tripType}/update-active-status', [TripTypeController::class, 'updateActiveStatus']);
	Route::get('trip_types/{tripType}/page', [TripTypeController::class, 'getTripTypePage']);
	Route::post('trip_types/{tripType}/update-page', [TripTypeController::class, 'updateTripTypePage']);

	//destinations
	Route::get('destinations/restore/{id}', [DestinationController::class, 'restore']);
	Route::get('destinations/force-delete/{id}', [DestinationController::class, 'forceDelete']);
	Route::patch('destinations/restore-multiple', [DestinationController::class, 'massRestore']);
	Route::patch('destinations/force-delete-multiple', [DestinationController::class, 'massForceDelete']);
	Route::patch('destinations/delete-multiple', [DestinationController::class, 'massDestroy']);
	Route::apiResource('destinations', DestinationController::class);
	Route::put('destinations/{destination}/update-active-status', [DestinationController::class, 'updateActiveStatus']);
	Route::get('destinations/{destination}/page', [DestinationController::class, 'getDestinationPage']);
	Route::post('destinations/{destination}/update-page', [DestinationController::class, 'updateDestinationPage']);

	//Locations
	Route::get('locations/restore/{id}', [LocationController::class, 'restore']);
	Route::get('locations/force-delete/{id}', [LocationController::class, 'forceDelete']);
	Route::patch('locations/restore-multiple', [LocationController::class, 'massRestore']);
	Route::patch('locations/force-delete-multiple', [LocationController::class, 'massForceDelete']);
	Route::patch('locations/delete-multiple', [LocationController::class, 'massDestroy']);
	Route::apiResource('locations', LocationController::class);
	Route::put('locations/{location}/update-active-status', [LocationController::class, 'updateActiveStatus']);
	Route::get('locations/{location}/page', [LocationController::class, 'getLocationPage']);
	Route::post('locations/{location}/update-page', [LocationController::class, 'updateLocationPage']);

	//Location Pickups
	Route::get('location_pickup/restore/{id}', [LocationPickupController::class, 'restore']);
	Route::get('location_pickup/force-delete/{id}', [LocationPickupController::class, 'forceDelete']);
	Route::patch('location_pickup/restore-multiple', [LocationPickupController::class, 'massRestore']);
	Route::patch('location_pickup/force-delete-multiple', [LocationPickupController::class, 'massForceDelete']);
	Route::patch('location_pickup/delete-multiple', [LocationPickupController::class, 'massDestroy']);
	Route::apiResource('location_pickup', LocationPickupController::class);
	Route::put('location_pickup/{locationPickup}/update-active-status', [LocationPickupController::class, 'updateActiveStatus']);

	//Location Addons
	Route::get('location_addon/restore/{id}', [LocationAddonController::class, 'restore']);
	Route::get('location_addon/force-delete/{id}', [LocationAddonController::class, 'forceDelete']);
	Route::patch('location_addon/restore-multiple', [LocationAddonController::class, 'massRestore']);
	Route::patch('location_addon/force-delete-multiple', [LocationAddonController::class, 'massForceDelete']);
	Route::patch('location_addon/delete-multiple', [LocationAddonController::class, 'massDestroy']);
	Route::apiResource('location_addon', LocationAddonController::class);
	Route::put('location_addon/{locationAddon}/update-active-status', [LocationAddonController::class, 'updateActiveStatus']);

	//Location Day
	Route::get('location_days/restore/{id}', [LocationDayController::class, 'restore']);
	Route::get('location_days/force-delete/{id}', [LocationDayController::class, 'forceDelete']);
	Route::patch('location_days/restore-multiple', [LocationDayController::class, 'massRestore']);
	Route::patch('location_days/force-delete-multiple', [LocationDayController::class, 'massForceDelete']);
	Route::patch('location_days/delete-multiple', [LocationDayController::class, 'massDestroy']);
	Route::apiResource('location_days', LocationDayController::class);
	Route::put('location_days/{locationDay}/update-active-status', [LocationDayController::class, 'updateActiveStatus']);

	//Trip
	Route::get('trip_dates', [TripController::class, 'tripDates']);
	Route::get('trips/restore/{id}', [TripController::class, 'restore']);
	Route::get('trips/force-delete/{id}', [TripController::class, 'forceDelete']);
	Route::patch('trips/restore-multiple', [TripController::class, 'massRestore']);
	Route::patch('trips/force-delete-multiple', [TripController::class, 'massForceDelete']);
	Route::patch('trips/delete-multiple', [TripController::class, 'massDestroy']);
	Route::apiResource('trips', TripController::class);
	Route::put('trips/{trip}/update-active-status', [TripController::class, 'updateActiveStatus']);
	Route::get('trips/{location}/partner-email', [TripController::class, 'partnerEmail']);

	//Trip Tickets
	Route::get('trip_tickets/restore/{id}', [TripTicketController::class, 'restore']);
	Route::get('trip_tickets/force-delete/{id}', [TripTicketController::class, 'forceDelete']);
	Route::patch('trip_tickets/restore-multiple', [TripTicketController::class, 'massRestore']);
	Route::patch('trip_tickets/force-delete-multiple', [TripTicketController::class, 'massForceDelete']);
	Route::patch('trip_tickets/delete-multiple', [TripTicketController::class, 'massDestroy']);
	Route::apiResource('trip_tickets', TripTicketController::class);

	//Trip Ticket Users
	Route::get('trip_ticket_users/restore/{id}', [TripTicketUserController::class, 'restore']);
	Route::get('trip_ticket_users/force-delete/{id}', [TripTicketUserController::class, 'forceDelete']);
	Route::patch('trip_ticket_users/restore-multiple', [TripTicketUserController::class, 'massRestore']);
	Route::patch('trip_ticket_users/force-delete-multiple', [TripTicketUserController::class, 'massForceDelete']);
	Route::patch('trip_ticket_users/delete-multiple', [TripTicketUserController::class, 'massDestroy']);
	Route::apiResource('trip_ticket_users', TripTicketUserController::class);

	//Trip Document
	Route::get('trip_documents/restore/{id}', [TripDocumentController::class, 'restore']);
	Route::get('trip_documents/force-delete/{id}', [TripDocumentController::class, 'forceDelete']);
	Route::patch('trip_documents/restore-multiple', [TripDocumentController::class, 'massRestore']);
	Route::patch('trip_documents/force-delete-multiple', [TripDocumentController::class, 'massForceDelete']);
	Route::patch('trip_documents/delete-multiple', [TripDocumentController::class, 'massDestroy']);
	Route::apiResource('trip_documents', TripDocumentController::class);
	Route::put('trip_documents/{tripDocument}/update-active-status', [TripDocumentController::class, 'updateActiveStatus']);

	//Trip Templete
	Route::get('trip_templates/restore/{id}', [TripTemplateController::class, 'restore']);
	Route::get('trip_templates/force-delete/{id}', [TripTemplateController::class, 'forceDelete']);
	Route::patch('trip_templates/restore-multiple', [TripTemplateController::class, 'massRestore']);
	Route::patch('trip_templates/force-delete-multiple', [TripTemplateController::class, 'massForceDelete']);
	Route::patch('trip_templates/delete-multiple', [TripTemplateController::class, 'massDestroy']);
	Route::apiResource('trip_templates', TripTemplateController::class);
	Route::put('trip_templates/{tripTemplate}/update-active-status', [TripTemplateController::class, 'updateActiveStatus']);

	//Tour Guide Infos
	Route::get('tour_guide_infos/restore/{id}', [TourGuideInfoController::class, 'restore']);
	Route::get('tour_guide_infos/force-delete/{id}', [TourGuideInfoController::class, 'forceDelete']);
	Route::patch('tour_guide_infos/restore-multiple', [TourGuideInfoController::class, 'massRestore']);
	Route::patch('tour_guide_infos/force-delete-multiple', [TourGuideInfoController::class, 'massForceDelete']);
	Route::patch('tour_guide_infos/delete-multiple', [TourGuideInfoController::class, 'massDestroy']);
	Route::apiResource('tour_guide_infos', TourGuideInfoController::class);
	Route::put('tour_guide_infos/{tourGuideInfo}/update-active-status', [TourGuideInfoController::class, 'updateActiveStatus']);

	//Reminder
	Route::get('reminders/restore/{id}', [ReminderController::class, 'restore']);
	Route::get('reminders/force-delete/{id}', [ReminderController::class, 'forceDelete']);
	Route::patch('reminders/restore-multiple', [ReminderController::class, 'massRestore']);
	Route::patch('reminders/force-delete-multiple', [ReminderController::class, 'massForceDelete']);
	Route::patch('reminders/delete-multiple', [ReminderController::class, 'massDestroy']);
	Route::apiResource('reminders', ReminderController::class);
	Route::put('reminders/{reminder}/update-active-status', [ReminderController::class, 'updateActiveStatus']);

	//Travel Agents
	Route::get('travel_agents/restore/{id}', [TravelAgentController::class, 'restore']);
	Route::get('travel_agents/force-delete/{id}', [TravelAgentController::class, 'forceDelete']);
	Route::patch('travel_agents/restore-multiple', [TravelAgentController::class, 'massRestore']);
	Route::patch('travel_agents/force-delete-multiple', [TravelAgentController::class, 'massForceDelete']);
	Route::patch('travel_agents/delete-multiple', [TravelAgentController::class, 'massDestroy']);
	Route::apiResource('travel_agents', TravelAgentController::class);
	Route::put('travel_agents/{travelAgent}/update-active-status', [TravelAgentController::class, 'updateActiveStatus']);

	//Travel Admins
	Route::get('travel_admins/restore/{id}', [TravelAdminController::class, 'restore']);
	Route::get('travel_admins/force-delete/{id}', [TravelAdminController::class, 'forceDelete']);
	Route::patch('travel_admins/restore-multiple', [TravelAdminController::class, 'massRestore']);
	Route::patch('travel_admins/force-delete-multiple', [TravelAdminController::class, 'massForceDelete']);
	Route::patch('travel_admins/delete-multiple', [TravelAdminController::class, 'massDestroy']);
	Route::apiResource('travel_admins', TravelAdminController::class);
	Route::put('travel_admins/{travelBrand}/update-active-status', [TravelAdminController::class, 'updateActiveStatus']);

	//Travel Brand
	Route::get('travel_brands/restore/{id}', [TravelBrandController::class, 'restore']);
	Route::get('travel_brands/force-delete/{id}', [TravelBrandController::class, 'forceDelete']);
	Route::patch('travel_brands/restore-multiple', [TravelBrandController::class, 'massRestore']);
	Route::patch('travel_brands/force-delete-multiple', [TravelBrandController::class, 'massForceDelete']);
	Route::patch('travel_brands/delete-multiple', [TravelBrandController::class, 'massDestroy']);
	Route::apiResource('travel_brands', TravelBrandController::class);
	Route::put('travel_brands/{travelBrand}/update-active-status', [TravelBrandController::class, 'updateActiveStatus']);

	//Trip Booking
	Route::get('trip_bookings_with_user', [TripBookingController::class, 'bookingUser']);
	Route::get('trip_bookings/restore/{id}', [TripBookingController::class, 'restore']);
	Route::get('trip_bookings/force-delete/{id}', [TripBookingController::class, 'forceDelete']);
	Route::patch('trip_bookings/restore-multiple', [TripBookingController::class, 'massRestore']);
	Route::patch('trip_bookings/force-delete-multiple', [TripBookingController::class, 'massForceDelete']);
	Route::patch('trip_bookings/delete-multiple', [TripBookingController::class, 'massDestroy']);
	Route::apiResource('trip_bookings', TripBookingController::class);
	Route::put('trip_bookings/{tripBooking}/update-active-status', [TripBookingController::class, 'updateActiveStatus']);
	Route::put('trip_bookings/{tripBooking}/update-policy-number', [TripBookingController::class, 'updatePolicyNumber']);

	//Trip Booking Notes
	Route::get('trip_booking_note/restore/{id}', [TripBookingNoteController::class, 'restore']);
	Route::get('trip_booking_note/force-delete/{id}', [TripBookingNoteController::class, 'forceDelete']);
	Route::patch('trip_booking_note/restore-multiple', [TripBookingNoteController::class, 'massRestore']);
	Route::patch('trip_booking_note/force-delete-multiple', [TripBookingNoteController::class, 'massForceDelete']);
	Route::patch('trip_booking_note/delete-multiple', [TripBookingNoteController::class, 'massDestroy']);
	Route::apiResource('trip_booking_note', TripBookingNoteController::class);
	Route::put('trip_booking_note/{tripBookingNote}/update-active-status', [TripBookingNoteController::class, 'updateActiveStatus']);

	//Trip Booking Documents
	Route::get('trip_booking_document/restore/{id}', [TripBookingDocumentController::class, 'restore']);
	Route::get('trip_booking_document/force-delete/{id}', [TripBookingDocumentController::class, 'forceDelete']);
	Route::patch('trip_booking_document/restore-multiple', [TripBookingDocumentController::class, 'massRestore']);
	Route::patch('trip_booking_document/force-delete-multiple', [TripBookingDocumentController::class, 'massForceDelete']);
	Route::patch('trip_booking_document/delete-multiple', [TripBookingDocumentController::class, 'massDestroy']);
	Route::apiResource('trip_booking_document', TripBookingDocumentController::class);
	Route::put('trip_booking_document/{tripBookingDocument}/update-active-status', [TripBookingDocumentController::class, 'updateActiveStatus']);

	//Trip Booking Addon
	Route::get('trip_booking_addon/restore/{id}', [TripBookingAddonController::class, 'restore']);
	Route::get('trip_booking_addon/force-delete/{id}', [TripBookingAddonController::class, 'forceDelete']);
	Route::patch('trip_booking_addon/restore-multiple', [TripBookingAddonController::class, 'massRestore']);
	Route::patch('trip_booking_addon/force-delete-multiple', [TripBookingAddonController::class, 'massForceDelete']);
	Route::patch('trip_booking_addon/delete-multiple', [TripBookingAddonController::class, 'massDestroy']);
	Route::apiResource('trip_booking_addon', TripBookingAddonController::class);
	Route::put('trip_booking_addon/{tripBookingAddon}/update-active-status', [TripBookingAddonController::class, 'updateActiveStatus']);

	//Trip Booking Payment
	Route::get('trip_booking_payment/restore/{id}', [TripBookingPaymentController::class, 'restore']);
	Route::get('trip_booking_payment/force-delete/{id}', [TripBookingPaymentController::class, 'forceDelete']);
	Route::patch('trip_booking_payment/restore-multiple', [TripBookingPaymentController::class, 'massRestore']);
	Route::patch('trip_booking_payment/force-delete-multiple', [TripBookingPaymentController::class, 'massForceDelete']);
	Route::patch('trip_booking_payment/delete-multiple', [TripBookingPaymentController::class, 'massDestroy']);
	Route::apiResource('trip_booking_payment', TripBookingPaymentController::class, ['except' => ['index']]);
	Route::get('trip_booking_payment', [TripBookingPaymentController::class, 'index']);
	Route::get('trip_booking_payments/{trip_booking_id}', [TripBookingPaymentController::class, 'index']);
	Route::put('trip_booking_payment/{tripBookingPayment}/update-active-status', [TripBookingPaymentController::class, 'updateActiveStatus']);

	//Trip Booking Extra Insurance
	Route::get('trip_booking_extra_insurances/restore/{id}', [TripBookingExtraInsuranceController::class, 'restore']);
	Route::get('trip_booking_extra_insurances/force-delete/{id}', [TripBookingExtraInsuranceController::class, 'forceDelete']);
	Route::patch('trip_booking_extra_insurances/restore-multiple', [TripBookingExtraInsuranceController::class, 'massRestore']);
	Route::patch('trip_booking_extra_insurances/force-delete-multiple', [TripBookingExtraInsuranceController::class, 'massForceDelete']);
	Route::patch('trip_booking_extra_insurances/delete-multiple', [TripBookingExtraInsuranceController::class, 'massDestroy']);
	Route::apiResource('trip_booking_extra_insurances', TripBookingExtraInsuranceController::class);
	Route::put('trip_booking_extra_insurances/{tripBookingExtraInsurance}/update-active-status', [TripBookingExtraInsuranceController::class, 'updateActiveStatus']);

	//Review
	Route::get('reviews/restore/{id}', [ReviewController::class, 'restore']);
	Route::get('reviews/force-delete/{id}', [ReviewController::class, 'forceDelete']);
	Route::patch('reviews/restore-multiple', [ReviewController::class, 'massRestore']);
	Route::patch('reviews/force-delete-multiple', [ReviewController::class, 'massForceDelete']);
	Route::patch('reviews/delete-multiple', [ReviewController::class, 'massDestroy']);
	Route::apiResource('reviews', ReviewController::class);
	Route::put('reviews/{review}/update-active-status', [ReviewController::class, 'updateActiveStatus']);

	//Trip Group
	Route::get('trip_groups/restore/{id}', [TripGroupController::class, 'restore']);
	Route::get('trip_groups/force-delete/{id}', [TripGroupController::class, 'forceDelete']);
	Route::patch('trip_groups/restore-multiple', [TripGroupController::class, 'massRestore']);
	Route::patch('trip_groups/force-delete-multiple', [TripGroupController::class, 'massForceDelete']);
	Route::patch('trip_groups/delete-multiple', [TripGroupController::class, 'massDestroy']);
	Route::apiResource('trip_groups', TripGroupController::class);
	Route::put('trip_groups/{tripGroup}/update-active-status', [TripGroupController::class, 'updateActiveStatus']);

	//Airline
	Route::get('airlines/restore/{id}', [AirlineController::class, 'restore']);
	Route::get('airlines/force-delete/{id}', [AirlineController::class, 'forceDelete']);
	Route::patch('airlines/restore-multiple', [AirlineController::class, 'massRestore']);
	Route::patch('airlines/force-delete-multiple', [AirlineController::class, 'massForceDelete']);
	Route::patch('airlines/delete-multiple', [AirlineController::class, 'massDestroy']);
	Route::apiResource('airlines', AirlineController::class);
	Route::put('airlines/{airline}/update-active-status', [AirlineController::class, 'updateActiveStatus']);

	//Reservation
	Route::get('reservations/restore/{id}', [ReservationController::class, 'restore']);
	Route::get('reservations/force-delete/{id}', [ReservationController::class, 'forceDelete']);
	Route::patch('reservations/restore-multiple', [ReservationController::class, 'massRestore']);
	Route::patch('reservations/force-delete-multiple', [ReservationController::class, 'massForceDelete']);
	Route::patch('reservations/delete-multiple', [ReservationController::class, 'massDestroy']);
	Route::apiResource('reservations', ReservationController::class);
	Route::put('reservations/{reservation}/update-active-status', [ReservationController::class, 'updateActiveStatus']);

	//Reservation Note
	Route::get('reservation_notes/restore/{id}', [ReservationNoteController::class, 'restore']);
	Route::get('reservation_notes/force-delete/{id}', [ReservationNoteController::class, 'forceDelete']);
	Route::patch('reservation_notes/restore-multiple', [ReservationNoteController::class, 'massRestore']);
	Route::patch('reservation_notes/force-delete-multiple', [ReservationNoteController::class, 'massForceDelete']);
	Route::patch('reservation_notes/delete-multiple', [ReservationNoteController::class, 'massDestroy']);
	Route::apiResource('reservation_notes', ReservationNoteController::class);
	Route::put('reservation_notes/{reservationNote}/update-active-status', [ReservationNoteController::class, 'updateActiveStatus']);

	//Blog Categories
	Route::get('blog_categories/restore/{id}', [BlogCategoryController::class, 'restore']);
	Route::get('blog_categories/force-delete/{id}', [BlogCategoryController::class, 'forceDelete']);
	Route::patch('blog_categories/restore-multiple', [BlogCategoryController::class, 'massRestore']);
	Route::patch('blog_categories/force-delete-multiple', [BlogCategoryController::class, 'massForceDelete']);
	Route::patch('blog_categories/delete-multiple', [BlogCategoryController::class, 'massDestroy']);
	Route::apiResource('blog_categories', BlogCategoryController::class);
	Route::put('blog_categories/{blogCategory}/update-active-status', [BlogCategoryController::class, 'updateActiveStatus']);

	//Blogs
	Route::get('blogs/restore/{id}', [BlogController::class, 'restore']);
	Route::get('blogs/force-delete/{id}', [BlogController::class, 'forceDelete']);
	Route::patch('blogs/restore-multiple', [BlogController::class, 'massRestore']);
	Route::patch('blogs/force-delete-multiple', [BlogController::class, 'massForceDelete']);
	Route::patch('blogs/delete-multiple', [BlogController::class, 'massDestroy']);
	Route::apiResource('blogs', BlogController::class);
	Route::put('blogs/{blog}/update-active-status', [BlogController::class, 'updateActiveStatus']);

	//Support Category
	Route::get('support_categories/restore/{id}', [SupportCategoryController::class, 'restore']);
	Route::get('support_categories/force-delete/{id}', [SupportCategoryController::class, 'forceDelete']);
	Route::patch('support_categories/restore-multiple', [SupportCategoryController::class, 'massRestore']);
	Route::patch('support_categories/force-delete-multiple', [SupportCategoryController::class, 'massForceDelete']);
	Route::patch('support_categories/delete-multiple', [SupportCategoryController::class, 'massDestroy']);
	Route::apiResource('support_categories', SupportCategoryController::class);
	Route::put('support_categories/{supportCategory}/update-active-status', [SupportCategoryController::class, 'updateActiveStatus']);
	Route::get('support_categories/{supportCategory}/page', [SupportCategoryController::class, 'getSupportCategoryPage']);
	Route::post('support_categories/{supportCategory}/update-page', [SupportCategoryController::class, 'updateSupportCategoryPage']);

	//Support Article
	Route::get('support_articles/restore/{id}', [SupportArticleController::class, 'restore']);
	Route::get('support_articles/force-delete/{id}', [SupportArticleController::class, 'forceDelete']);
	Route::patch('support_articles/restore-multiple', [SupportArticleController::class, 'massRestore']);
	Route::patch('support_articles/force-delete-multiple', [SupportArticleController::class, 'massForceDelete']);
	Route::patch('support_articles/delete-multiple', [SupportArticleController::class, 'massDestroy']);
	Route::apiResource('support_articles', SupportArticleController::class);
	Route::put('support_articles/{supportArticle}/update-active-status', [SupportArticleController::class, 'updateActiveStatus']);
	Route::get('support_articles/{supportArticle}/page', [SupportArticleController::class, 'getSupportArticlePage']);
	Route::post('support_articles/{supportArticle}/update-page', [SupportArticleController::class, 'updateSupportArticlePage']);

	//Forum Category
	Route::get('forum_categories/restore/{id}', [ForumCategoryController::class, 'restore']);
	Route::get('forum_categories/force-delete/{id}', [ForumCategoryController::class, 'forceDelete']);
	Route::patch('forum_categories/restore-multiple', [ForumCategoryController::class, 'massRestore']);
	Route::patch('forum_categories/force-delete-multiple', [ForumCategoryController::class, 'massForceDelete']);
	Route::patch('forum_categories/delete-multiple', [ForumCategoryController::class, 'massDestroy']);
	Route::apiResource('forum_categories', ForumCategoryController::class);
	Route::put('forum_categories/{forumCategory}/update-active-status', [ForumCategoryController::class, 'updateActiveStatus']);

	//Forum Topic
	Route::get('forum_topics/restore/{id}', [ForumTopicController::class, 'restore']);
	Route::get('forum_topics/force-delete/{id}', [ForumTopicController::class, 'forceDelete']);
	Route::patch('forum_topics/restore-multiple', [ForumTopicController::class, 'massRestore']);
	Route::patch('forum_topics/force-delete-multiple', [ForumTopicController::class, 'massForceDelete']);
	Route::patch('forum_topics/delete-multiple', [ForumTopicController::class, 'massDestroy']);
	Route::apiResource('forum_topics', ForumTopicController::class);
	Route::put('forum_topics/{forumTopic}/update-active-status', [ForumTopicController::class, 'updateActiveStatus']);

	//Forum Reply
	Route::get('forum_replies/restore/{id}', [ForumReplyController::class, 'restore']);
	Route::get('forum_replies/force-delete/{id}', [ForumReplyController::class, 'forceDelete']);
	Route::patch('forum_replies/restore-multiple', [ForumReplyController::class, 'massRestore']);
	Route::patch('forum_replies/force-delete-multiple', [ForumReplyController::class, 'massForceDelete']);
	Route::patch('forum_replies/delete-multiple', [ForumReplyController::class, 'massDestroy']);
	Route::apiResource('forum_replies', ForumReplyController::class);
	Route::put('forum_replies/{forumReply}/update-active-status', [ForumReplyController::class, 'updateActiveStatus']);

	//Config Page
	Route::get('config_pages/restore/{id}', [ConfigPageController::class, 'restore']);
	Route::get('config_pages/force-delete/{id}', [ConfigPageController::class, 'forceDelete']);
	Route::patch('config_pages/restore-multiple', [ConfigPageController::class, 'massRestore']);
	Route::patch('config_pages/force-delete-multiple', [ConfigPageController::class, 'massForceDelete']);
	Route::patch('config_pages/delete-multiple', [ConfigPageController::class, 'massDestroy']);
	Route::apiResource('config_pages', ConfigPageController::class);
	Route::put('config_pages/{configPage}/update-active-status', [ConfigPageController::class, 'updateActiveStatus']);
	Route::get('config-page-variable/{configPage}/get-variables',[ConfigPageController::class,'getVariables']);
	Route::post('config_page/{configPage}/save-variables',[ConfigPageController::class,'saveVariables']);

	//Config Variable
	Route::get('config_variables/restore/{id}', [ConfigVariableController::class, 'restore']);
	Route::get('config_variables/force-delete/{id}', [ConfigVariableController::class, 'forceDelete']);
	Route::patch('config_variables/restore-multiple', [ConfigVariableController::class, 'massRestore']);
	Route::patch('config_variables/force-delete-multiple', [ConfigVariableController::class, 'massForceDelete']);
	Route::patch('config_variables/delete-multiple', [ConfigVariableController::class, 'massDestroy']);
	Route::apiResource('config_variables', ConfigVariableController::class);
	Route::put('config_variables/{configPage}/update-active-status', [ConfigVariableController::class, 'updateActiveStatus']);

	//Tour Guide Trips
	Route::get('trip_tour_guide/restore/{id}', [TripTourGuideController::class, 'restore']);
	Route::get('trip_tour_guide/force-delete/{id}', [TripTourGuideController::class, 'forceDelete']);
	Route::patch('trip_tour_guide/restore-multiple', [TripTourGuideController::class, 'massRestore']);
	Route::patch('trip_tour_guide/force-delete-multiple', [TripTourGuideController::class, 'massForceDelete']);
	Route::patch('trip_tour_guide/delete-multiple', [TripTourGuideController::class, 'massDestroy']);
	Route::apiResource('trip_tour_guide', TripTourGuideController::class);
	Route::put('trip_tour_guide/{tripTourGuide}/update-active-status', [TripTourGuideController::class, 'updateActiveStatus']);

	//Courses
	Route::get('courses/restore/{id}', [CourseController::class, 'restore']);
	Route::get('courses/force-delete/{id}', [CourseController::class, 'forceDelete']);
	Route::patch('courses/restore-multiple', [CourseController::class, 'massRestore']);
	Route::patch('courses/force-delete-multiple', [CourseController::class, 'massForceDelete']);
	Route::patch('courses/delete-multiple', [CourseController::class, 'massDestroy']);
	Route::apiResource('courses', CourseController::class);

	//Lesson
	Route::get('lessons/restore/{id}', [LessonController::class, 'restore']);
	Route::get('lessons/force-delete/{id}', [LessonController::class, 'forceDelete']);
	Route::patch('lessons/restore-multiple', [LessonController::class, 'massRestore']);
	Route::patch('lessons/force-delete-multiple', [LessonController::class, 'massForceDelete']);
	Route::patch('lessons/delete-multiple', [LessonController::class, 'massDestroy']);
	Route::apiResource('lessons', LessonController::class);

	//Quiz Question
	Route::get('quiz_questions/restore/{id}', [QuizQuestionController::class, 'restore']);
	Route::get('quiz_questions/force-delete/{id}', [QuizQuestionController::class, 'forceDelete']);
	Route::patch('quiz_questions/restore-multiple', [QuizQuestionController::class, 'massRestore']);
	Route::patch('quiz_questions/force-delete-multiple', [QuizQuestionController::class, 'massForceDelete']);
	Route::patch('quiz_questions/delete-multiple', [QuizQuestionController::class, 'massDestroy']);
	Route::apiResource('quiz_questions', QuizQuestionController::class);

	//Quiz Question Answer
	Route::get('quiz_question_answers/restore/{id}', [QuizQuestionAnswerController::class, 'restore']);
	Route::get('quiz_question_answers/force-delete/{id}', [QuizQuestionAnswerController::class, 'forceDelete']);
	Route::patch('quiz_question_answers/restore-multiple', [QuizQuestionAnswerController::class, 'massRestore']);
	Route::patch('quiz_question_answers/force-delete-multiple', [QuizQuestionAnswerController::class, 'massForceDelete']);
	Route::patch('quiz_question_answers/delete-multiple', [QuizQuestionAnswerController::class, 'massDestroy']);
	Route::apiResource('quiz_question_answers', QuizQuestionAnswerController::class);

	//UsetNote
	Route::get('user_notes/restore/{id}', [UserNoteController::class, 'restore']);
	Route::get('user_notes/force-delete/{id}', [UserNoteController::class, 'forceDelete']);
	Route::patch('user_notes/restore-multiple', [UserNoteController::class, 'massRestore']);
	Route::patch('user_notes/force-delete-multiple', [UserNoteController::class, 'massForceDelete']);
	Route::patch('user_notes/delete-multiple', [UserNoteController::class, 'massDestroy']);
	Route::apiResource('user_notes', UserNoteController::class);
	Route::put('user_notes/{userNote}/update-active-status', [UserNoteController::class, 'updateActiveStatus']);

	//Landing Pages
	Route::get('landing_pages/restore/{id}', [LandingPageController::class, 'restore']);
	Route::get('landing_pages/force-delete/{id}', [LandingPageController::class, 'forceDelete']);
	Route::patch('landing_pages/restore-multiple', [LandingPageController::class, 'massRestore']);
	Route::patch('landing_pages/force-delete-multiple', [LandingPageController::class, 'massForceDelete']);
	Route::patch('landing_pages/delete-multiple', [LandingPageController::class, 'massDestroy']);
	Route::apiResource('landing_pages', LandingPageController::class);
	Route::put('landing_pages/{landingPage}/update-active-status', [LandingPageController::class, 'updateActiveStatus']);
	Route::get('landing_pages/{landingPage}/page', [LandingPageController::class, 'getLandingPage']);
	Route::post('landing_pages/{landingPage}/update-page', [LandingPageController::class, 'updateLandingPage']);

	//Page Redirect
	Route::get('page-redirects/restore/{id}', [PageRedirectController::class, 'restore']);
	Route::get('page-redirects/force-delete/{id}', [PageRedirectController::class, 'forceDelete']);
	Route::patch('page-redirects/restore-multiple', [PageRedirectController::class, 'massRestore']);
	Route::patch('page-redirects/force-delete-multiple', [PageRedirectController::class, 'massForceDelete']);
	Route::patch('page-redirects/delete-multiple', [PageRedirectController::class, 'massDestroy']);
	Route::apiResource('page-redirects', PageRedirectController::class);
	Route::put('page-redirects/{pageRedirect}/update-active-status', [PageRedirectController::class, 'updateActiveStatus']);

	//Email Template
	Route::get('email_templates/restore/{id}', [EmailTemplateController::class, 'restore']);
	Route::get('email_templates/force-delete/{id}', [EmailTemplateController::class, 'forceDelete']);
	Route::patch('email_templates/restore-multiple', [EmailTemplateController::class, 'massRestore']);
	Route::patch('email_templates/force-delete-multiple', [EmailTemplateController::class, 'massForceDelete']);
	Route::patch('email_templates/delete-multiple', [EmailTemplateController::class, 'massDestroy']);
	Route::apiResource('email_templates', EmailTemplateController::class);
	Route::put('email_templates/{emailTemplate}/update-active-status', [EmailTemplateController::class, 'updateActiveStatus']);

	//Email Template Condition
	Route::get('email_template_conditions/restore/{id}', [EmailTemplateConditionController::class, 'restore']);
	Route::get('email_template_conditions/force-delete/{id}', [EmailTemplateConditionController::class, 'forceDelete']);
	Route::patch('email_template_conditions/restore-multiple', [EmailTemplateConditionController::class, 'massRestore']);
	Route::patch('email_template_conditions/force-delete-multiple', [EmailTemplateConditionController::class, 'massForceDelete']);
	Route::patch('email_template_conditions/delete-multiple', [EmailTemplateConditionController::class, 'massDestroy']);
	Route::apiResource('email_template_conditions', EmailTemplateConditionController::class);
	Route::put('email_template_conditions/{emailTemplateCondition}/update-active-status', [EmailTemplateConditionController::class, 'updateActiveStatus']);

	//Uploads
	Route::apiResource('uploads', UploadController::class);

	//CSV
	Route::get('email_downloads', [TripBookingController::class, 'downloadEmails']);
	Route::get('passport_downloads', [TripBookingController::class, 'downloadPassport']);
	Route::get('ticket_downloads', [TripBookingController::class, 'downloadTicket']);
	Route::get('csv_downloads', [TripBookingController::class, 'downloadCsv']);
	Route::get('insurance_downloads', [TripBookingController::class, 'downloadInsurance']);
	Route::get('trip_booking_addons', [TripBookingController::class, 'downloadAddon']);
	Route::get('csv_reservation', [TripBookingController::class, 'downloadReservation']);

	Route::get('depositReminder/{id}', [HelperController::class, 'depositReminder']);
	Route::get('passwordEmail/{id}', [HelperController::class, 'passwordEmail']);
	Route::get('passportReminder/{id}', [HelperController::class, 'passportReminder']);

	//Login To The dashboard
	Route::post('front-login', function(Request $request){
		
		if(in_array('view-myaccount', auth()->user()->given_permissions) || auth()->user()->id == 1){
			if($request->has("user_id")){
				Session::put('authUserId', $request->get("user_id"));
				$user = User::where('id', $request->get("user_id"))->first();
				return ["status" => true, "user" => $user];
			}
		}
		return ["status" => false];
	});
	Route::get('dashboard', [HomeController::class, 'dashboard']);
	Route::get('booking-details/{tripBooking}', [HomeController::class, 'dashboardBooking']);
	Route::post('save-whatsapp-number', [HomeController::class, 'saveWhatsappNumber']);
    Route::post('save-passport-details', [HomeController::class, 'savePassportDetails']);
	Route::post('save-insurance', [HomeController::class, 'saveInsurance']);
	Route::get('forum-topics', [HomeController::class, 'forumTopics']);
	Route::get('single-topic/{forumTopic}', [HomeController::class, 'singleTopic']);
	Route::post('save-comment', [HomeController::class, 'saveComment']);
	Route::post('update-profile', [HomeController::class, 'updateProfile']);
	Route::post('save-addon', [HomeController::class, 'saveAddon']);
	Route::get('get-insurances', [HomeController::class, 'getInsurance']);

	Route::get('guide-dashboard', [TourGuideController::class, 'dashboard']);
	Route::get('guide-dashboard-details/{trip}', [TourGuideController::class, 'dashboardDetails']);
	Route::get('my-guides', [TourGuideController::class, 'myGuides']);
	Route::get('show-my-guide/{user}', [TourGuideController::class, 'showMyGuide']);
	Route::post('store-guide', [TourGuideController::class, 'storeGuide']);
	Route::put('update-guide/{user}', [TourGuideController::class, 'updateGuide']);
	Route::post('update-guide-trip', [TourGuideController::class, 'updateGuideTrip']);
	Route::get('guide-courses', [TourGuideController::class, 'course']);
	Route::get('guide-lessons/{course}', [TourGuideController::class, 'lesson']);
	Route::get('guide-lesson-intro/{lesson}', [TourGuideController::class, 'lessonIntro']);
	Route::get('guide-questions/{lesson}', [TourGuideController::class, 'lessonQuestion']);
	Route::get('previous-trips', [TourGuideController::class, 'previousTrip']);
	Route::post('dropbox-trip', [TourGuideController::class, 'dropboxTrip']);


	//Reports
	Route::get('reports/get-booking-numbers/{year}', [ReportController::class, 'getBookingNumbers']);
	Route::get('reports/get-booking-chart-data', [ReportController::class, 'getBookingChartData']);
	Route::get('reports/download/{location}/{year}/{date?}', [ReportController::class, 'downloadReports']);
	Route::get('reports/download/reservation/{location}/{year}/{date?}', [ReportController::class, 'downloadReportsReservation']);
	Route::post('reports/send-email', [ReportController::class, 'sendEmailReport']);
});
//front api's

Route::get('getSettings', [HelperController::class, 'getSetting']);
Route::get('general-configurations', [HomeController::class, 'getGeneralConfigurations']);
Route::get('months',[HomeController::class,'getMonths']);
Route::get('home',[HomeController::class,'getHomePageData']);
Route::get('page/{page}', [HomeController::class, 'page']);
Route::get('trip/{page}', [HomeController::class, 'trip']);
Route::post('save-booking', [HomeController::class, 'saveBooking']);
Route::get('getAttributes',[App\Http\Controllers\AttributeController::class,'getAttribute']);
Route::get('locationsByAgeGroup', [App\Http\Controllers\DestinationController::class,'getLocationByAgeGroup']);
//Route::get('ageGroup/{ageGroup}', [App\Http\Controllers\DestinationController::class,'getAgeGroupById']);
Route::get('getBlogs',[App\Http\Controllers\BlogController::class,'getBlog']);
Route::get('getSingleblog',[App\Http\Controllers\BlogController::class,'getSingleblog']);
Route::get('getTrips',[App\Http\Controllers\TripController::class,'trips']);
Route::get('getTripTypes',[App\Http\Controllers\TripController::class,'tripTypes']);
Route::get('trips-slider',[App\Http\Controllers\TripController::class,'tripSlider']);
Route::get('blog-post',[App\Http\Controllers\BlogController::class,'blogPost']);
Route::get('upcoming-trips',[App\Http\Controllers\TripController::class,'upcomingTrips']);
Route::get('upcoming-trips-spot',[App\Http\Controllers\TripController::class,'upcomingTripsSpot']);
Route::get('getSupportArticles',[App\Http\Controllers\ContactController::class,'getSupportArticle']);
Route::get('getSingleArticle',[App\Http\Controllers\ContactController::class,'getSingleArticle']);
Route::get('sitemap',[App\Http\Controllers\HelperController::class,'sitemap']);
Route::get('sitemap.xml',[App\Http\Controllers\HelperController::class,'sitemapXml']);
Route::get('booking-pdf/{tripBooking}',[App\Http\Controllers\HelperController::class,'bookingPDF']);
Route::get('download-booking-pdf/{tripBooking}',[App\Http\Controllers\HelperController::class,'downloadBookingPDF']);
Route::get('check-redirect-url',[App\Http\Controllers\Admin\PageRedirectController::class,'checkRedirectUrl']);
Route::post('save-reminder', [App\Http\Controllers\TripController::class, 'saveReminder']);