<?php

use Zhiyi\Plus\Http\Middleware;

// 获取手机验证码
Route::post('/auth/phone/send-code', 'AuthController@sendPhoneCode')
    ->middleware(Middleware\VerifyPhoneNumber::class) // 验证手机号格式是否正确
    ->middleware(Middleware\VerifySendPhoneCodeType::class) // 验证发送验证码类型等集合
;

// 用户登录
Route::post('/auth', 'AuthController@login')
    ->middleware(Middleware\CheckDeviceCodeExisted::class) // 验证设备号是否存在
    ->middleware(Middleware\VerifyPhoneNumber::class) // 验证手机号码是否正确
    ->middleware(Middleware\CheckUserByPhoneExisted::class) // 验证手机号码用户是否存在
;

// 重置token接口
Route::patch('/auth', 'AuthController@resetToken');

// 用户注册
Route::post('/auth/register', 'AuthController@register')
    ->middleware(Middleware\CheckDeviceCodeExisted::class) // 验证设备号是否存在
    ->middleware(Middleware\VerifyPhoneNumber::class) // 验证手机号码是否正确
    ->middleware(Middleware\VerifyUserNameRole::class) // 验证用户名规则是否正确
    ->middleware(Middleware\CheckUserByNameNotExisted::class) // 验证用户名是否被占用
    ->middleware(Middleware\CheckUserByPhoneNotExisted::class) // 验证手机号码是否被占用
    ->middleware(Middleware\VerifyPhoneCode::class) // 验证验证码释放正确
;

// 找回密码
Route::patch('/auth/forgot', 'AuthController@forgotPassword')
    ->middleware(Middleware\VerifyPhoneNumber::class) // 验证手机号格式
    ->middleware(Middleware\CheckUserByPhoneExisted::class) // 验证手机号码用户是否存在
    ->middleware(Middleware\VerifyPhoneCode::class) // 验证手机号码验证码是否正确
;

// 用户相关组
Route::prefix('users')
->middleware('auth:api')
->group(function () {
    // 修改用户资料
    Route::patch('/', 'UserController@profile')
        ->middleware(Middleware\ChangeUserAvatar::class)
        ->middleware(Middleware\ChangeUsername::class);
    // 修改用户密码
    Route::patch('/password', 'UserController@resetPassword') // 设置控制器
        ->middleware(Middleware\VerifyPassword::class); // 验证用户密码是否正确
    // 获取用户信息
    Route::get('/{user}', 'UserController@get');
});

// 获取一个附件资源
Route::get('/storages/{storage}/{process?}', 'StorageController@get');
// 附件储存相关
Route::group([
    'middleware' => 'auth:api',
    'prefix'     => 'storages',
], function () {
    // 创建一个储存任务
    Route::post('/task/{hash}/{origin_filename}', 'StorageController@create');
    // 完成一个任务上传通知
    Route::patch('/task/{storage_task_id}', 'StorageController@notice');
    // 删除一个上传任务附件
    Route::delete('/task/{storage_task_id}', 'StorageController@delete');
    // local storage api.
    Route::post('/task/{storage_task_id}', 'StorageController@upload')
        ->name('storage/upload');
});
