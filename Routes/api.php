<?php


Route::group([
    'as' => 'api.wordpress.',
    'prefix' => 'wordpress',
], function () {

    Route::prefix('woocommerce')->group(function () {

        // Opret ordre
        Route::post('order', 'Api\WordpressController@store')
            ->name('order.create');

        // Opdater ordre
        Route::patch('order', 'Api\WordpressController@update')
            ->name('order.update');

        // Slet ordre
        Route::delete('order', 'Api\WordpressController@destroy')
            ->name('order.destroy');

    });

});
