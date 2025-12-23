<?php
use think\facade\Route;

// 全局中间件（移除这种方式，通过配置文件注册）
// Route::middleware('token_auth');

// 用户相关路由 - 明确指定api前缀
Route::group('api/user', function () {
    Route::post('login', 'User/login')->name('api.user.login');
    Route::post('register', 'User/register')->name('api.user.register');
    Route::post('forget', 'User/forget')->name('api.user.forget');
    Route::post('sendcode', 'User/sendcode')->name('api.user.sendcode');
    Route::post('checkcode', 'User/checkcode')->name('api.user.checkcode');
    Route::post('logout', 'User/logout')->name('api.user.logout');
    Route::post('initGuest', 'User/initGuest')->name('api.user.initGuest');
    Route::post('guestBalance', 'User/guestBalance')->name('api.user.guestBalance');
})->prefix('app\\api\\controller\\');

// 机器人相关路由 - 直接指定带命名空间的控制器
Route::post('api/bot/start', 'app\api\controller\Bot@start');
Route::post('api/bot/stop', 'app\api\controller\Bot@stop');
Route::get('api/bot/status', 'app\api\controller\Bot@status');
Route::get('api/bot/list', 'app\api\controller\Bot@list');
Route::post('api/bot/buy', 'app\api\controller\Bot@buy');
Route::post('api/bot/sell', 'app\api\controller\Bot@sell');
Route::get('api/bot/holding', 'app\api\controller\Bot@holding');

// 交易订单相关路由（解决Linux大小写敏感问题）
Route::any('api/tradeOrder/placeOrderGuest', 'app\api\controller\TradeOrder@placeOrderGuest');
Route::any('api/tradeOrder/pendingOrdersGuest', 'app\api\controller\TradeOrder@pendingOrdersGuest');
Route::any('api/tradeOrder/historyOrdersGuest', 'app\api\controller\TradeOrder@historyOrdersGuest');
Route::any('api/tradeOrder/checkOrderStatus', 'app\api\controller\TradeOrder@checkOrderStatus');
Route::any('api/tradeOrder/placeOrder', 'app\api\controller\TradeOrder@placeOrder');
Route::any('api/tradeOrder/pendingOrders', 'app\api\controller\TradeOrder@pendingOrders');
Route::any('api/tradeOrder/historyOrders', 'app\api\controller\TradeOrder@historyOrders');

// 交易对相关路由（解决Linux大小写敏感问题）
Route::any('api/tradePair/list', 'app\api\controller\TradePair@list');
Route::any('api/tradePair/detail', 'app\api\controller\TradePair@detail');

// 兼容旧版路由
Route::group('user', function () {
    Route::post('login', 'User/login')->name('user.login');
    Route::post('register', 'User/register')->name('user.register');
    Route::post('forget', 'User/forget')->name('user.forget');
    Route::post('sendcode', 'User/sendcode')->name('user.sendcode');
    Route::post('checkcode', 'User/checkcode')->name('user.checkcode');
    Route::post('logout', 'User/logout')->name('user.logout');
}); 