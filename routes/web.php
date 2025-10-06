<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Admin\StudentRegistrationController;
use App\Http\Controllers\Admin\StudentImportController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Student\StudentBookingController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\VideoSessionController;
use App\Http\Controllers\Student\ScheduleController;

/*
|--------------------------------------------------------------------------
| 🔐 Public Route: Login Page (Root)
|--------------------------------------------------------------------------
| Loads the login page using Breeze's AuthenticatedSessionController.
| Connected to: resources/views/auth/login.blade.php
*/
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isStudent()) {
            return redirect()->route('student.dashboard');
        } elseif ($user->isFacilitator()) {
            return redirect()->route('facilitator.dashboard');
        }
    }

    // If not logged in, show login page
    return app(\App\Http\Controllers\Auth\AuthenticatedSessionController::class)->create(request());
})->name('login');

/*
|--------------------------------------------------------------------------
| 🔒 Admin-Only Route: Register New User
|--------------------------------------------------------------------------
| Only authenticated users with 'admin' role can access.
| Controller: RegisteredUserController (App\Http\Controllers\Auth\)
| Views: resources/views/auth/register.blade.php
*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/admin/dashboard/register', [RegisteredUserController::class, 'store']);
});



/*
|--------------------------------------------------------------------------
| 🔐 Authenticated Routes (All Roles)
|--------------------------------------------------------------------------
| All routes below require the user to be logged in.
*/
Route::middleware('auth')->group(function () {

    // 👤 Admin Dashboard
    // View: resources/views/admin/dashboard.blade.php
    Route::get('/admin/dashboard', fn() => view('admin.dashboard'))->name('admin.dashboard');

    // 👨‍🎓 Student Dashboard
    // View: resources/views/student/dashboard.blade.php
    Route::get('/student/dashboard', fn() => view('student.dashboard'))->name('student.dashboard');

    // 👥 Peer Facilitator Dashboard
    // View: resources/views/facilitator/dashboard.blade.php
    Route::get('/facilitator/dashboard', fn() => view('facilitator.dashboard'))->name('facilitator.dashboard');


    // ⚙️ Profile Management (Edit, Update, Delete)
    // Controller: ProfileController (App\Http\Controllers\)
    // Views: resources/views/profile/edit.blade.php
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


//Register New Student (Admin Only)
// Controller: StudentRegistrationController (App\Http\Controllers\Admin\)
// Views: resources/views/admin/register-student.blade.php


Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/register-student', [StudentRegistrationController::class, 'create'])->name('admin.student.create');
    Route::post('/admin/register-student', [StudentRegistrationController::class, 'store'])->name('admin.student.store');
});

// Import Students (Admin Only)
// Controller: StudentImportController (App\Http\Controllers\Admin\)
// Views: resources/views/admin/upload-students.blade.php

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/upload-students', [StudentImportController::class, 'index'])->name('admin.upload.page');
    Route::post('/admin/upload-students', [StudentImportController::class, 'store'])->name('admin.upload.students');
});


// Download Sample CSV Template (Admin Only)
use Illuminate\Support\Facades\Response;
Route::get('/admin/upload-sample-csv', function () {
    $headers = ['Content-Type' => 'text/csv'];
    $callback = function () {
        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['name', 'email', 'password', 'psu_id', 'course']);
        fputcsv($handle, ['Juan Dela Cruz', 'juan@psu.edu.ph', 'password123', '22-AC-0001', 'BSIT']);
        fclose($handle);
    };
    return response()->stream($callback, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="student_template.csv"',
    ]);
})->name('admin.download.template');


// Admin Calendar Management
// Controller: CalendarController (App\Http\Controllers\Admin\)
// Views: resources/views/admin/calendar.blade.php
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::post('/admin/calendar/event', [CalendarController::class, 'store'])->name('calendar.event.store');
    Route::delete('/admin/calendar/event/{id}', [CalendarController::class, 'destroy'])->name('calendar.delete');
});


// Recurring Events Management
// Controller: CalendarController (App\Http\Controllers\Admin\)
// Views: resources/views/admin/calendar.blade.php
Route::middleware(['auth', 'role:admin'])->group(function () {
Route::post('/admin/calendar/recurring', [CalendarController::class, 'storeRecurring'])->name('calendar.recurring.store');
Route::delete('/admin/calendar/recurring/{id}', [CalendarController::class, 'destroyRecurring'])->name('calendar.recurring.delete');
});


// Student Booking Routes
// Controller: StudentBookingController (App\Http\Controllers\Student\)
// Views: resources/views/student/booking/form.blade.php, resources/views/student/booking/counselors.blade.php

Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/booking/counselors', [StudentBookingController::class, 'index'])->name('booking.counselors');
    Route::get('/booking/create/{counselor_id}', [StudentBookingController::class, 'create'])->name('booking.form');
    Route::post('/booking/store', [StudentBookingController::class, 'store'])->name('booking.store');
});



// routes/web.php
// Admin Booking Management
// Controller: BookingController (App\Http\Controllers\Admin\)
// Views: resources/views/admin/bookings/index.blade.php, resources/views/admin/bookings/show.blade.php

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{id}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{id}/status', [BookingController::class, 'updateStatus'])->name('bookings.status.update');
    Route::post('/bookings/{id}/notes', [BookingController::class, 'saveNotes'])->name('bookings.notes.save');
    Route::post('/calendar/store', [BookingController::class, 'scheduleFollowUp'])->name('calendar.store');
     Route::post('/bookings/{id}/status', [BookingController::class, 'updateStatus'])->name('bookings.status.update');
});


Route::middleware(['auth','role:admin'])->group(function(){
    Route::get('/admin/video/start/{booking}', [VideoSessionController::class,'start'])->name('video.start');
});

Route::middleware(['auth','role:student'])->group(function() {
    Route::get('/student/video/join/{booking}', [VideoSessionController::class,'join'])->name('video.join');
});


// Student Schedule Management
// Controller: ScheduleController (App\Http\Controllers\Student\)
// Views: resources/views/student/schedule/index.blade.php
Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('student.schedule.index');
    Route::put('/student/booking/{id}', [App\Http\Controllers\Student\StudentBookingController::class, 'update'])->name('student.booking.update');
    Route::delete('/student/booking/{id}',[App\Http\Controllers\Student\StudentBookingController::class, 'cancel'])->name('student.booking.cancel');
    Route::get('/student/counselor/{id}/schedule/{date}', [App\Http\Controllers\Student\StudentBookingController::class, 'getSchedule']);
});

// routes/web.php
// Admin
// Controller: BookingController (App\Http\Controllers\Admin\)
// Views: resources/views/admin/bookings/show.blade.php
Route::middleware(['auth','role:admin'])->group(function(){

Route::post('/admin/bookings/{id}/reschedule', [BookingController::class,'requestReschedule'])->name('bookings.reschedule');
Route::post('/admin/bookings/{id}/rebook', [BookingController::class,'rebook'])->name('bookings.rebook');
Route::get('/admin/schedule/{date}', [BookingController::class, 'getSchedule']);

});

// Student
Route::middleware(['auth','role:student'])->group(function(){
Route::post('/student/bookings/{id}/reschedule/accept', [StudentBookingController::class,'acceptReschedule'])->name('student.reschedule.accept');
Route::post('/student/bookings/{id}/reschedule/decline', [StudentBookingController::class,'declineReschedule'])->name('student.reschedule.decline');
});


/*
|--------------------------------------------------------------------------
| 📦 Breeze Authentication Routes
|--------------------------------------------------------------------------
| Includes login, logout, password reset, etc.
| File: routes/auth.php (auto-loaded)
*/
require __DIR__.'/auth.php';


