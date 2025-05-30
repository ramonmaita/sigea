<?php

use App\Http\Controllers\PdfController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

Route::get('/requisiciones/{requisicion}/pdf', [PdfController::class, 'generarRequisicionPdf'])
    ->name('requisiciones.pdf')
    ->middleware('auth'); // Asegurar que solo usuarios autenticados puedan acceder

Route::get('/ordenes-salida/{ordenSalida}/pdf', [PdfController::class, 'generarOrdenSalidaPdf'])
    ->name('ordenes_salida.pdf')
    ->middleware('auth'); // Proteger la ruta

    Route::get('/solicitudes-desincorporacion/{solicitud}/acta-pdf', [PdfController::class, 'generarActaDesincorporacionPdf'])
    ->name('solicitudes_desincorporacion.acta_pdf')
    ->middleware('auth');

Route::get('/solicitudes-reasignacion/{solicitud}/acta-pdf', [PdfController::class, 'generarActaReasignacionPdf'])
    ->name('solicitudes_reasignacion.acta_pdf')
    ->middleware('auth');

    Route::get('/comunicaciones/{comunicacion}/pdf', [PdfController::class, 'generarComunicacionPdf'])
    ->name('comunicaciones.pdf')
    ->middleware('auth');

require __DIR__ . '/auth.php';
