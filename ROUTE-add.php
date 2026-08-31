<?php

// ===================================================================
// Добавьте ОДНУ строку внутрь группы admin в routes/web.php,
// рядом с другими run/* маршрутами:
//
//   Route::post('run/sync-euro', [DashboardController::class, 'syncEuro'])->name('run.syncEuro');
//
// Она должна оказаться внутри:
//   Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () { ... });
// ===================================================================
