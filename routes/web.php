<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

// Public Cyclocross Pages
Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('/page/{slug}', [PublicPageController::class, 'showPage'])->name('pages.show');
Route::get('/events', [PublicPageController::class, 'events'])->name('events.index');
Route::get('/events/{event}', [PublicPageController::class, 'showEvent'])->name('events.show');
Route::get('/results', [PublicPageController::class, 'results'])->name('results.index');
Route::get('/standings', [PublicPageController::class, 'standings'])->name('standings.index');

// Public Racer Registration
Route::get('/register-race', [RegistrationController::class, 'create'])->name('register.create');
Route::post('/register-race', [RegistrationController::class, 'store'])->name('register.store');

// Authenticated Admin Dashboard & Management
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // CMS Pages Management
    Route::get('/admin/pages', [AdminController::class, 'pages'])->name('admin.pages.index');
    Route::get('/admin/pages/create', [AdminController::class, 'createPage'])->name('admin.pages.create');
    Route::post('/admin/pages', [AdminController::class, 'storePage'])->name('admin.pages.store');
    Route::get('/admin/pages/{page}/edit', [AdminController::class, 'editPage'])->name('admin.pages.edit');
    Route::put('/admin/pages/{page}', [AdminController::class, 'updatePage'])->name('admin.pages.update');
    Route::delete('/admin/pages/{page}', [AdminController::class, 'deletePage'])->name('admin.pages.destroy');

    // Timing Entry & Results Management
    Route::get('/admin/results/entry', [AdminController::class, 'resultsEntry'])->name('admin.results.entry');
    Route::post('/admin/results', [AdminController::class, 'storeResults'])->name('admin.results.store');

    // Registrations Management
    Route::get('/admin/registrations', [AdminController::class, 'registrations'])->name('admin.registrations.index');
    Route::put('/admin/registrations/{registration}', [AdminController::class, 'updateRegistration'])->name('admin.registrations.update');
    Route::delete('/admin/registrations/{registration}', [AdminController::class, 'deleteRegistration'])->name('admin.registrations.destroy');

    // Race Day Check-In & Clothespin Assignment
    Route::get('/admin/check-in', [AdminController::class, 'checkIn'])->name('admin.checkin.index');
    Route::post('/admin/check-in/day-of', [AdminController::class, 'storeDayOfRegistration'])->name('admin.checkin.dayof');
    Route::post('/admin/check-in/{registration}', [AdminController::class, 'processCheckIn'])->name('admin.checkin.process');

    // Race Events & Categories Management
    Route::get('/admin/events', [AdminController::class, 'events'])->name('admin.events.index');
    Route::post('/admin/events', [AdminController::class, 'storeEvent'])->name('admin.events.store');
    Route::put('/admin/events/{event}', [AdminController::class, 'updateEvent'])->name('admin.events.update');
    Route::delete('/admin/events/{event}', [AdminController::class, 'deleteEvent'])->name('admin.events.destroy');

    Route::post('/admin/categories', [AdminController::class, 'storeCategory'])->name('admin.categories.store');
    Route::put('/admin/categories/{category}', [AdminController::class, 'updateCategory'])->name('admin.categories.update');
    Route::delete('/admin/categories/{category}', [AdminController::class, 'deleteCategory'])->name('admin.categories.destroy');

    // Admin Account Management
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users.index');
    Route::post('/admin/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::put('/admin/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.users.destroy');
});

require __DIR__.'/settings.php';
