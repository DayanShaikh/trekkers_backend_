<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HelperController;
use Illuminate\HTTP\Request;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/{any}', function ($any, Request $request) {
    if(strpos($any, 'sitemap.xml')!==false){
        return HelperController::sitemapXml($request);
    }
})->where('any', '.*');
Route::view('loginn','login');
Route::get('rabobank-response', [HomeController::class, 'rabobankResponse']);

// Auth::routes();
// Route::middleware(['auth'])->group(function(){
//     Route::get('activity',[ActivityController::class,'index']);
// });

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
