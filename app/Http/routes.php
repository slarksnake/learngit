<?php

Route::group(
    [
        'namespace' => 'Admin\V1',
        'prefix' => 'web/v1',
        'middleware' => []
    ],

    function () {
        //材料资料
        Route::group(['prefix' => 'material'], function () {
            Route::get('/', 'InventoryManage@index');
            Route::get('/{id}', 'InventoryManage@select');
//            Route::post('/', 'MaterialController@create');
//            Route::put('/{id}', 'MaterialController@update');
//            Route::delete('/{id}', 'MaterialController@delete');

                //Route::resource('category', 'MaterialCateController');材料分类资料
            Route::group(['prefix' => 'category'], function () {
                Route::get('/', 'MaterialCateController@index');
                Route::post('/', 'MaterialCateController@create');
                Route::put('/{id}', 'MaterialCateController@update');
                Route::delete('/{id}', 'MaterialCateController@delete');
            });

            //申请材料资源
            Route::group(['prefix' => 'apply'], function () {
                Route::get('/', 'ApplyManageController@index');
                Route::put('/{id}', 'ApplyManageController@cancel');
                //采购申请资源
                Route::group(['prefix' => 'procurement'], function () {
                    Route::get('/{id}', 'ApplyManageController@selectProcurement');
                    Route::post('/', 'ApplyManageController@createProcurement');
                    Route::put('/{id}', 'ApplyManageController@auditProcurement');
                });
                //领用申请资源
                Route::group(['prefix' => 'recipients'], function () {
                    Route::get('/{id}', 'ApplyManageController@selectRecipients');
                    Route::post('/', 'ApplyManageController@createRecipients');
                    Route::put('/{id}', 'ApplyManageController@auditRecipients');
                });
            });

            //购买管理资源
            Route::group(['prefix' => 'purchase'], function () {
                Route::post('/', 'PurchaseManageController@createOrders');
                //库存资源
                Route::group(['prefix' => 'storage'], function () {
                    Route::get('/', 'PurchaseManageController@listStorage');
                    Route::post('/', 'PurchaseManageController@createStorage');
                    Route::put('/{id}', 'PurchaseManageController@updateStorage');
                });
                //报销资源
                Route::group(['prefix' => 'reimburse'], function () {
                    Route::get('/', 'PurchaseManageController@listReimburse');
                    Route::put('/{id}', 'PurchaseManageController@reimburseStorage');
                });

            });
        });

        Route::group(['prefix' => 'project'], function () {
            //项目管理资料
            Route::group(['prefix' => 'category'], function () {
                Route::get('/', 'ProjectCateController@index');
                Route::post('/', 'ProjectCateController@create');
                Route::put('/{id}', 'ProjectCateController@update');
                Route::delete('/{id}', 'ProjectCateController@delete');
            });
        });
        //供货商资料
        Route::group(['prefix' => 'supplier'], function () {
            Route::get('/', 'SupplierController@index');
            Route::get('/{id}', 'SupplierController@select');
            Route::post('/', 'SupplierController@create');
            Route::put('/{id}', 'SupplierController@update');
            Route::delete('/{id}', 'SupplierController@delete');
        });
});






/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/
//
//Route::group(['middleware' => ['web']], function () {
//    //
//});
