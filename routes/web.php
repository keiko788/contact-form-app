<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ContactController::class, 'index'])->name('contact.index');
Route::post('contacts/confirm', [ContactController::class, 'confirm'])->name('contact.confirm');
Route::post('contacts', [ContactController::class, 'store'])->name('contact.store');
Route::get('/thanks', [ContactController::class, 'thanks'])->name('contact.thanks');

// 認証が必要な管理画面ルート
Route::middleware('auth')
    ->prefix('admin')
    ->group(function () {
        Route::get('/', [AdminController::class, 'index']);
        Route::get('/contacts/{contact}', [AdminController::class, 'show']);
        Route::delete('/contacts/{contact}', [AdminController::class, 'destroy']);

        Route::post('/tags', [TagController::class, 'store']);
        Route::get('/tags/{tag}/edit', [TagController::class, 'edit']);
        Route::put('/tags/{tag}', [TagController::class, 'update']);
        Route::delete('/tags/{tag}', [TagController::class, 'destroy']);
    });

// CSVエクスポート
Route::middleware('auth')->group(function () {
    Route::get('/contacts/export', [ContactController::class, 'export']);
});
