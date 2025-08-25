<?php
// Temporary fix - redirect /admin to /vici which is working
Route::get('/admin', function () {
    return redirect('/vici');
});









