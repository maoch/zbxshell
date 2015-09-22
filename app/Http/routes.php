<?php


Route::get('/', 'ZbxTopController@getLogin');
Route::post('login', 'ZbxTopController@postLogin');
Route::get('logout', 'ZbxTopController@getLogout');

// MAIN PAGES

Route::get('home', 'ZbxTopController@dashboardIndex');

//搜索设备列表,分页显示时，使用get方式提交，所以添加get
Route::get('search', 'ZbxTopController@deviceIndex');
Route::post('search', 'ZbxTopController@deviceIndex');


Route::group(['prefix' => 'devices'], function () {
    Route::get('/', 'ZbxTopController@deviceIndex');
    Route::get('/{ciname}', 'ZbxTopController@deviceIndex');

    Route::get('event/show/{name}/{hostid}', 'ZbxTopController@eventShow');
    Route::get('event/show/{name}', 'ZbxTopController@eventShow');

    Route::get('monitor/show/{name}/{hostid}', 'ZbxTopController@monitorShow');
    Route::get('monitor/show/{name}', 'ZbxTopController@monitorShow');

    Route::get('history/show', 'ZbxTopController@historyShow');
    Route::get('history/show/{name}', 'ZbxTopController@historyShow');
    Route::get('history/show/{name}/{hostid}/{itemid}/{itemName}', 'ZbxTopController@historyShow');
});

Route::group(['prefix' => 'maps'], function () {
    Route::get('/{selectedId}', 'ZbxTopController@mapIndex');
    Route::get('/', 'ZbxTopController@mapIndex');
});
//事件
Route::group(['prefix' => 'events'], function () {
    Route::get('/{hostid}', 'ZbxTopController@eventIndex');
    Route::get('/', 'ZbxTopController@eventIndex');
});

