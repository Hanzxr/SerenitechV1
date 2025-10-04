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
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Response;

/*
|--------------------------------------------------------------------------
| Public Route: Login Page (Root)
|--------------------------------------------------------------------------
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

    return app(\App\Http\Controllers\Auth\AuthenticatedSessionController::class)
        ->create(request());
})->name('login');

/*
|--------------------------------------------------------------------------
| Authenticated Routes (All Roles)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Admin Dashboard
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
        ->middleware('role:admin')
        ->name('admin.dashboard');

    // Student Dashboard
    Route::get('/student/dashboard', fn() => view('student.dashboard'))
        ->name('student.dashboard');

    // Facilitator Dashboard
    Route::get('/facilitator/dashboard', fn() => view('facilitator.dashboard'))
        ->name('facilitator.dashboard');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {

    // User Registration
    Route::get('/dashboard/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/dashboard/register', [RegisteredUserController::class, 'store']);

    // Posts Management
    Route::get('/posts', [PostController::class, 'index'])->name('admin.posts.index');
    Route::get('/posts/create', [PostController::class, 'create'])->name('admin.posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('admin.posts.store');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('admin.posts.destroy');

    // Student Registration
    Route::get('/register-student', [StudentRegistrationController::class, 'create'])->name('admin.student.create');
    Route::post('/register-student', [StudentRegistrationController::class, 'store'])->name('admin.student.store');

    // Upload Students
    Route::get('/upload-students', [StudentImportController::class, 'index'])->name('admin.upload.page');
    Route::post('/upload-students', [StudentImportController::class, 'store'])->name('admin.upload.students');

    // Download Sample CSV
    Route::get('/upload-sample-csv', function () {
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

    // Calendar Management
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::post('/calendar/event', [CalendarController::class, 'store'])->name('calendar.event.store');
    Route::delete('/calendar/event/{id}', [CalendarController::class, 'destroy'])->name('calendar.delete');

    // Recurring Events
    Route::post('/calendar/recurring', [CalendarController::class, 'storeRecurring'])->name('calendar.recurring.store');
    Route::delete('/calendar/recurring/{id}', [CalendarController::class, 'destroyRecurring'])->name('calendar.recurring.delete');

    // Booking Management
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{id}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{id}/status', [BookingController::class, 'updateStatus'])->name('bookings.status.update');
    Route::post('/bookings/{id}/notes', [BookingController::class, 'saveNotes'])->name('bookings.notes.save');
    Route::post('/calendar/store', [BookingController::class, 'scheduleFollowUp'])->name('calendar.store');

    // Video Session Start
    Route::get('/video/start/{booking}', [VideoSessionController::class,'start'])->name('video.start');
});

/*
|--------------------------------------------------------------------------
| Student Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {

    // Dashboard already defined in auth group
    // Booking
    Route::get('/booking/counselors', [StudentBookingController::class, 'index'])->name('booking.counselors');
    Route::get('/booking/create/{counselor_id}', [StudentBookingController::class, 'create'])->name('booking.form');
    Route::post('/booking/store', [StudentBookingController::class, 'store'])->name('booking.store');

    // Student Announcements
    Route::get('/announcements', [PostController::class, 'studentView'])->name('announcements');

    // Video Session Join
    Route::get('/video/join/{booking}', [VideoSessionController::class,'join'])->name('video.join');

    // Schedule
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');

    // Counselor Schedule
    Route::get('/counselor/{id}/schedule/{date}', [StudentBookingController::class, 'getSchedule']);
});

/*
|--------------------------------------------------------------------------
| Include Breeze Authentication Routes
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
