<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\API\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


 Route::post('login', [App\Http\Controllers\API\Auth\AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('checkPassword', [App\Http\Controllers\API\User\UserProfileCotroller::class, 'index'])->name('checkPassword');
    Route::post('changePassword', [App\Http\Controllers\API\User\UserProfileCotroller::class, 'change_password'])->name('changePassword');
    Route::post('resetPassword', [App\Http\Controllers\API\User\UserProfileCotroller::class, 'reset_password'])->name('resetPassword');
    Route::get('logsFunction', [App\Http\Controllers\API\User\UserProfileCotroller::class, 'logs_function'])->name('logsFunction');

    Route::resource('assert', App\Http\Controllers\API\Setup\AssertController::class);
    Route::resource('adminHierarchies', App\Http\Controllers\API\Setup\AdminHierarchiesController::class);
    Route::resource('workStations', App\Http\Controllers\API\Setup\WorkingStationsController::class);
    Route::resource('uploadTypes', App\Http\Controllers\API\Setup\UploadTypesController::class);
    Route::resource('locations', App\Http\Controllers\API\Setup\GeographicalLocationsController::class);
    Route::resource('identifications', App\Http\Controllers\API\Setup\IdentificationsController::class);
    Route::resource('countries', App\Http\Controllers\API\Setup\CountriesController::class);
    
    Route::resource('userAccounts', App\Http\Controllers\API\User\UsersCotroller::class);
    Route::resource('roles', App\Http\Controllers\API\User\RolesCotroller::class);
    Route::resource('permissions', App\Http\Controllers\API\User\PermissionsCotroller::class);

    Route::get('getDefaultHeadCount', [App\Http\Controllers\API\Management\DashboardController::class, 'get_default_year'])->name('getDefaultHeadCount');
    Route::get('getSelectedHeadCount/{year}', [App\Http\Controllers\API\Management\DashboardController::class, 'get_selected_year'])->name('getSelectedHeadCount');
    Route::get('unBlockIdentifications/{identifications_id}', [App\Http\Controllers\API\Setup\UnBlockCotroller::class, 'unblock_identifications'])->name('unBlockIdentifications');
    Route::get('unBlockGeographicalLocations/{geographical_locations_id}', [App\Http\Controllers\API\Setup\UnBlockCotroller::class, 'unblock_geographical_locations'])->name('unBlockGeographicalLocations');
    Route::get('unBlockUploadTypes/{upload_types_id}', [App\Http\Controllers\API\Setup\UnBlockCotroller::class, 'unblock_upload_types'])->name('unBlockUploadTypes');
    Route::get('unBlockUser/{user_id}', [App\Http\Controllers\API\Setup\UnBlockCotroller::class, 'unblock_user'])->name('unBlockUser');
    
    Route::resource('departments', App\Http\Controllers\API\Setup\DepartmentController::class);
    Route::resource('assets', App\Http\Controllers\API\Setup\AssetController::class);
    Route::resource('conditions', App\Http\Controllers\API\Setup\ConditionController::class);

    Route::get('assetDepartment', [App\Http\Controllers\API\Management\Module\Asserts\AssertController::class, 'asset_department'])->name('assetDepartment');
    Route::post('storeAssertDepartment', [App\Http\Controllers\API\Management\Module\Asserts\AssertController::class, 'store_assert_department'])->name('storeAssertDepartment');
    Route::get('viewAssertDepartment', [App\Http\Controllers\API\Management\Module\Asserts\AssertController::class, 'view_assert_department'])->name('viewAssertDepartment');
    Route::patch('updateAssertDepartment', [App\Http\Controllers\API\Management\Module\Asserts\AssertController::class, 'update_assert_department'])->name('updateAssertDepartment');
    Route::delete('destroyAssertDepartment', [App\Http\Controllers\API\Management\Module\Asserts\AssertController::class, 'destroy_assert_department'])->name('destroyAssertDepartment');
    Route::post('transferAssert', [App\Http\Controllers\API\Management\Module\Asserts\AssertController::class, 'transfer_assert'])->name('transferAssert');


    Route::get('viewChallenge', [ App\Http\Controllers\API\Management\Module\ProblemSolution\ChallengeController::class, 'view_challenge'])->name('viewChallenge');
    Route::post('storeChallenge', [ App\Http\Controllers\API\Management\Module\ProblemSolution\ChallengeController::class, 'store_challenge'])->name('storeChallenge');
    Route::get('showChallenge/{chalenge_id}', [ App\Http\Controllers\API\Management\Module\ProblemSolution\ChallengeController::class, 'show_challenge'])->name('showChallenge');
    Route::delete('destroyChallenge', [ App\Http\Controllers\API\Management\Module\ProblemSolution\ChallengeController::class, 'destroy_challenge'])->name('destroyChallenge');
    Route::post('storeSolution', [ App\Http\Controllers\API\Management\Module\ProblemSolution\ChallengeController::class, 'store_solution'])->name('storeSolution');
    Route::delete('destroySolution', [ App\Http\Controllers\API\Management\Module\ProblemSolution\ChallengeController::class, 'destroy_solution'])->name('destroySolution');

    Route::get('getDefaultHeadCount', [App\Http\Controllers\API\Management\DashboardController::class, 'get_default_year'])->name('getDefaultHeadCount');
    Route::get('getDefaultHeadCount', [App\Http\Controllers\API\Management\DashboardController::class, 'get_default_year'])->name('getDefaultHeadCount');
    Route::get('getDefaultHeadCount', [App\Http\Controllers\API\Management\DashboardController::class, 'get_default_year'])->name('getDefaultHeadCount');


});
