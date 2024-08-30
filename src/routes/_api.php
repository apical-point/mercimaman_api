<?php

// use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['prefix'=>'v1'], function () {

    // ---------------------------- 管理者専用のアクセス ----------------------------
    // 管理者 Auth
    Route::middleware('multiauth:admin')->post('admin/auth', 'Api\AdminAuthController@store'); // 新規登録
    Route::middleware('multiauth:admin')->get('admin/auth/getMyData', 'Api\AdminAuthController@getMyData'); // 自分のデータ取得
    Route::middleware('multiauth:admin')->put('admin/auth/update', 'Api\AdminAuthController@update'); // 自分のデータ更新
    Route::middleware('multiauth:admin')->put('admin/auth/updatePassword', 'Api\AdminAuthController@updatePassword'); // 自分のパスワード
    Route::middleware('multiauth:admin')->delete('admin/auth/withdrawal', 'Api\AdminAuthController@withdrawal'); // 退会
    Route::post('admin/auth/loginCheck', 'Api\AdminAuthController@loginCheck'); // ログインのバリデートのみ
    Route::put('admin/auth/sendResetPasswordMail', 'Api\AdminAuthController@sendResetPasswordMail'); // ログインのバリデートのみ

    // 管理者
    Route::middleware('multiauth:admin')->resource('admin/adminers', 'Api\AdminerController'); // 管理者

    // ユーザー
    Route::middleware('multiauth:admin')->resource('admin/users', 'Api\UserController'); // ユーザー
    Route::middleware('multiauth:admin')->delete('admin/auth/userwithdrawal', 'Api\AuthController@withdrawal'); // 退会

    // 商品
    Route::middleware('multiauth:admin')->resource('admin/products', 'Api\ProductController'); // 商品
    Route::middleware('multiauth:admin')->resource('admin/product-categories', 'Api\ProductCategoryController'); // 商品カテゴリー
    Route::middleware('multiauth:admin')->post('admin/product-categories/sortUpdate', 'Api\ProductCategoryController@sortupdate'); // 商品カテゴリー並び替え
    Route::middleware('multiauth:admin')->post('admin/products/update/{id?}', 'Api\ProductController@update'); // 自分の商品編集
    Route::middleware('multiauth:admin')->get('admin/product/getMessage', 'Api\ProductController@getMessage'); // メッセージの取得
    Route::middleware('multiauth:admin')->post('admin/product/setMessage', 'Api\ProductController@setMessage'); // メッセージの設定
    Route::middleware('multiauth:admin')->post('admin/product/updateMessage/{id}', 'Api\ProductController@updateMessage'); // メッセージの設定
    Route::middleware('multiauth:admin')->post('admin/product/updateStatus/{id}', 'Api\ProductController@updateStatus'); // ステータス変更

    // 注文
    Route::middleware('multiauth:admin')->get('admin/orders/getpayment', 'Api\OrderController@getpayment'); // ステータスの変更
    Route::middleware('multiauth:admin')->post('admin/orders/updateStatus', 'Api\OrderController@updateStatus'); // ステータスの変更
    Route::middleware('multiauth:admin')->post('admin/orders/updStatusall', 'Api\OrderController@updStatusall'); // ステータスの変更
    Route::middleware('multiauth:admin')->get('admin/orders/salesList', 'Api\OrderController@adminsalesList'); // 月・店舗別検索機能付き売り上げリスト
    Route::middleware('multiauth:admin')->get('admin/orders/salesDetails', 'Api\OrderController@salesDetails'); // 月・店舗別売り上げリスト詳細取得
    Route::middleware('multiauth:admin')->put('admin/orders/orderUpdPostage', 'Api\OrderController@orderUpdPostage'); // 送料の更新（複数）

    Route::middleware('multiauth:admin')->resource('admin/orders', 'Api\OrderController'); // 注文

    //都道府県
	Route::middleware('multiauth:admin')->resource('admin/prefectures', 'Api\PrefectureController');

    //各種設定
    Route::middleware('multiauth:admin')->resource('admin/site_config', 'Api\SiteConfigController');

    //お問合せ
    Route::middleware('multiauth:admin')->resource('admin/inquiry', 'Api\InquiryController');

    //お知らせ
    Route::middleware('multiauth:admin')->resource('admin/news', 'Api\NewsController');

    //FAQ
    Route::middleware('multiauth:admin')->resource('admin/faq', 'Api\FaqController');

    //ポイント
    Route::middleware('multiauth:admin')->resource('admin/points', 'Api\PointController');

    //広告
    //Route::middleware('multiauth:admin')->resource('admin/advertisement', 'Api\AdvertisementController');
    Route::middleware('multiauth:admin')->get('admin/advertisement', 'Api\AdvertisementController@index'); // ユーザー詳細
    Route::middleware('multiauth:admin')->get('admin/advertisement/{id}', 'Api\AdvertisementController@show'); // ユーザー詳細
    Route::middleware('multiauth:admin')->post('admin/advertisement', 'Api\AdvertisementController@store'); // ユーザー詳細
    Route::middleware('multiauth:admin')->post('admin/advertisement/{id}', 'Api\AdvertisementController@update'); // ユーザー詳細
    Route::middleware('multiauth:admin')->delete('admin/advertisement/{id}', 'Api\AdvertisementController@destroy'); // ユーザー詳細

    //メッセージ
    Route::middleware('multiauth:admin')->resource('admin/message', 'Api\MessageController');

    //--------コンテンツ ------------------
    Route::middleware('multiauth:admin')->get('admin/content/getMessageContent', 'Api\ContentController@getMessageContent');
    Route::middleware('multiauth:admin')->get('admin/content/getOfferContent', 'Api\ContentController@getOfferContent');
    Route::middleware('multiauth:admin')->delete('admin/content/destroyOfferContent/{id}', 'Api\ContentController@destroyOfferContent');
    Route::middleware('multiauth:admin')->post('admin/content/updateMessage/{id}', 'Api\ContentController@updateMessage'); // メッセージの設定
    Route::middleware('multiauth:admin')->post('admin/content/updatePresent/{id}', 'Api\ContentController@updatePresent'); // プレゼントの当選結果

    //クロスワード
    Route::middleware('multiauth:admin')->post('admin/content/storeCrossword', 'Api\ContentController@storeCrossword'); // クロスワード登録
    Route::middleware('multiauth:admin')->put('admin/content/updateCrossword/{id}', 'Api\ContentController@updateCrossword'); // クロスワード 更新
    Route::middleware('multiauth:admin')->get('admin/content/indexCrossword', 'Api\ContentController@indexCrossword'); // クロスワード検索
    Route::middleware('multiauth:admin')->get('admin/content/showCrossword/{id}', 'Api\ContentController@showCrossword'); // クロスワード取得
    Route::middleware('multiauth:admin')->delete('admin/content/destroyCrossword/{id}', 'Api\ContentController@deleteCrossword'); // クロスワード削除

    //タロット
    Route::middleware('multiauth:admin')->post('admin/content/storeTarot', 'Api\ContentController@storeTarot'); // クロスワード登録
    Route::middleware('multiauth:admin')->put('admin/content/updateTarot/{id}', 'Api\ContentController@updateTarot'); // クロスワード 更新
    Route::middleware('multiauth:admin')->get('admin/content/indexTarot', 'Api\ContentController@indexTarot'); // クロスワード検索
    Route::middleware('multiauth:admin')->get('admin/content/showTarot/{id}', 'Api\ContentController@showTarot'); // クロスワード取得
    Route::middleware('multiauth:admin')->delete('admin/content/destroyTarot/{id}', 'Api\ContentController@deleteTarot'); // クロスワード削除

    Route::middleware('multiauth:admin')->get('admin/contents', 'Api\ContentController@index'); // ユーザー詳細
    Route::middleware('multiauth:admin')->get('admin/contents/{id}', 'Api\ContentController@show'); // ユーザー詳細
    Route::middleware('multiauth:admin')->post('admin/contents', 'Api\ContentController@store'); // ユーザー詳細
    Route::middleware('multiauth:admin')->post('admin/contents/{id}', 'Api\ContentController@update'); // ユーザー詳細
    Route::middleware('multiauth:admin')->delete('admin/contents/{id}', 'Api\ContentController@destroy'); // ユーザー詳細





    //----掲示板-----

    //体験
     Route::middleware('multiauth:admin')->post('admin/board/storeExp', 'Api\BoardController@storeExp'); // 要望、体験投稿
     Route::middleware('multiauth:admin')->put('admin/board/updateExp/{id}', 'Api\BoardController@updateExp'); // 要望、体験 更新
     Route::middleware('multiauth:admin')->get('admin/board/indexExp', 'Api\BoardController@indexExp'); // 検索
     Route::middleware('multiauth:admin')->get('admin/board/showExp/{id}', 'Api\BoardController@showExp'); // 体験取得
     Route::middleware('multiauth:admin')->get('admin/board/destroyExp/{id}', 'Api\BoardController@destroyExp'); // 体験削除

    //リクエスト
    Route::middleware('multiauth:admin')->resource('admin/board', 'Api\BoardController'); // リクエスト掲示板

    //---　ツイート機能 ---------
    Route::middleware('multiauth:admin')->get('admin/tweet/indexTree', 'Api\TweetController@indexTree'); //コメントツリー情報の取得
    Route::middleware('multiauth:admin')->resource('admin/tweet', 'Api\TweetController');


    //---  イベントカレンダー ---------
    Route::middleware('multiauth:admin')->resource('admin/event', 'Api\EventController');


    //各年齢で出来る事
    Route::middleware('multiauth:admin')->get('admin/growth/indexGrowthAge', 'Api\GrowthController@indexGrowthAge');//検索
    Route::middleware('multiauth:admin')->get('admin/growth/showGrowthAge/{id}', 'Api\GrowthController@showGrowthAge');//取得
    Route::middleware('multiauth:admin')->post('admin/growth/createGrowthAge', 'Api\GrowthController@createGrowthAge'); // 登録
    Route::middleware('multiauth:admin')->put('admin/growth/updateGrowthAge/{id}', 'Api\GrowthController@updateGrowthAge'); //  更新
    Route::middleware('multiauth:admin')->delete('admin/growth/destroyGrowthAge/{id}', 'Api\GrowthController@destroyGrowthAge');//削除
    Route::middleware('multiauth:admin')->delete('admin/growth/upFileGrowthDelete', 'Api\GrowthController@upFileGrowthDelete');//ファイルの削除



    //指定したユーザーの子ども達の成長記録の取得
    Route::middleware('multiauth:admin')->get('admin/growth/indexGrowthUserOne', 'Api\GrowthController@indexGrowthUserOne');//検索

    //---  成長カレンダー ---------
    Route::middleware('multiauth:admin')->resource('admin/growth', 'Api\GrowthController');


    // バッチテスト
    Route::put('admin/batch/daily', 'Api\BatchController@daily');
    Route::put('admin/batch/monthly', 'Api\BatchController@monthly');

    // ---------------------------- 一般ユーザー専用のアクセス ----------------------------
    // Auth
    Route::post('auth/entry', 'Api\AuthController@entry'); // 仮登録
    Route::post('auth/regist/{param?}', 'Api\AuthController@regist'); // 新規登録
    Route::get('auth/getEntryUserByParam/{param?}', 'Api\AuthController@getEntryUserByParam'); // パラメータでエントリーユーザー取得
    Route::get('auth/validateEmail', 'Api\AuthController@validateEmail'); // メールアドレスチェック

    Route::put('auth/sendResetPasswordMail', 'Api\AuthController@sendResetPasswordMail'); // パスワード再設定メール送信
    Route::get('auth/getResetPasswordUserByParam/{param?}', 'Api\AuthController@getResetPasswordUserByParam'); // パスワード再設定パラメータチェック
    Route::put('auth/resetPassword/{param?}', 'Api\AuthController@resetPassword'); // パスワード再設定
    Route::put('auth/updateEmail/{param?}', 'Api\AuthController@updateEmail'); // メールアドレス再設定

    Route::middleware('multiauth:api')->put('auth/sendChangeEmailMail', 'Api\AuthController@sendChangeEmailMail'); // メールアドレス再設定メール送信
    Route::middleware('multiauth:api')->delete('auth/withdrawal', 'Api\AuthController@withdrawal'); // 退会
    Route::middleware('multiauth:api')->put('auth/updatePassword', 'Api\AuthController@updatePassword'); // 自分のパスワードを更新
    Route::middleware('multiauth:api')->get('auth/getMyData', 'Api\AuthController@getMyData'); // 自分のデータ取得

    // ------------------------------------- StripeAPI -------------------------------------
	Route::middleware('multiauth:api')->get('stripe/getCard', 'Api\StripeController@getCard'); // カード取得
	Route::middleware('multiauth:api')->put('stripe/setCard', 'Api\StripeController@setCard'); // カード作成or更新
	Route::middleware('multiauth:api')->delete('stripe/deleteCard', 'Api\StripeController@deleteCard'); // カード消去

    // ユーザー
	Route::middleware('multiauth:api')->get('users/indexFavorite', 'Api\UserController@indexFavorite'); // ユーザーお気に入り、フォロー、フォロワー一覧

    Route::get('users', 'Api\UserController@index'); // ユーザー
    Route::get('users/{id}', 'Api\UserController@show'); // ユーザー詳細

    Route::middleware('multiauth:api')->post('users/{id}', 'Api\UserController@update'); // ユーザー詳細
    Route::middleware('multiauth:api')->post('users/setFavorite/{id}', 'Api\UserController@setFavorite'); // ユーザーお気に入り合計
    Route::middleware('multiauth:api')->get('users/getFavorite/{id?}', 'Api\UserController@getFavorite'); // お気に入り登録
    Route::middleware('multiauth:api')->get('user/getUserFavorite', 'Api\UserController@getUserFavorite'); // ユーザーお気に入り



    //ポイント
    Route::middleware('multiauth:api')->resource('points', 'Api\PointController'); // ポイント

    //お知らせ
    Route::middleware('multiauth:api')->resource('news', 'Api\NewsController'); // ポイント
    Route::middleware('multiauth:api')->get('news/getUserNews/{id}', 'Api\NewsController@getUserNews'); // ポイント

    //商品
    Route::get('products', 'Api\ProductController@index'); // ユーザー
    Route::get('products/{id}', 'Api\ProductController@show'); // ユーザー詳細
    Route::get('product-categories', 'Api\ProductCategoryController@index'); // 商品カテゴリ
    Route::get('product-categories/{id?}', 'Api\ProductCategoryController@show'); // 商品カテゴリ
    Route::get('product/getBrand', 'Api\ProductController@getBrand'); // ブランドの取得
    Route::get('product/getMessage', 'Api\ProductController@getMessage'); // メッセージの設定
    Route::middleware('multiauth:api')->post('products', 'Api\ProductController@store'); // ユーザー詳細
    Route::middleware('multiauth:api')->post('products/{id}', 'Api\ProductController@update'); // ユーザー詳細
    Route::middleware('multiauth:api')->post('product/setMessage', 'Api\ProductController@setMessage'); // メッセージの取得
    Route::middleware('multiauth:api')->put('product/updateMessage/{id}', 'Api\ProductController@updateMessage'); // メッセージの取得
    Route::middleware('multiauth:api')->delete('product/destroyMessage', 'Api\ProductController@destroyMessage'); // メッセージの削除
    Route::middleware('multiauth:api')->post('product/updateStatus/{id}', 'Api\ProductController@updateStatus'); // 取引内容更新

    //オーダー
    Route::middleware('multiauth:api')->get('orders/sellList', 'Api\OrderController@sellList'); // 売り上げリスト
    Route::middleware('multiauth:api')->post('orders/updateStatus', 'Api\OrderController@updateStatus'); // 購入リスト
    Route::middleware('multiauth:api')->get('orders/list', 'Api\OrderController@list'); // リスト
    Route::middleware('multiauth:api')->get('orders/getEvaluation/{id?}', 'Api\OrderController@getEvaluation'); // 取引評価
    Route::middleware('multiauth:api')->post('orders/{id}', 'Api\OrderController@update'); // ユーザー詳細
    Route::middleware('multiauth:api')->resource('orders', 'Api\OrderController'); // オーダー

    //----掲示板----------
    //体験
    Route::post('board/storeExp', 'Api\BoardController@storeExp'); // 要望、体験投稿
    Route::middleware('multiauth:api')->put('board/updateExp/{id}', 'Api\BoardController@updateExp'); // 要望、体験 更新
    Route::get('board/indexExp', 'Api\BoardController@indexExp'); // 検索未ログイン者もOK
    Route::middleware('multiauth:api')->get('board/showExp/{id}', 'Api\BoardController@showExp'); // 体験取得
    Route::middleware('multiauth:api')->delete('board/deleteExp', 'Api\BoardController@deleteExp'); // 体験削除

    //リクエスト
    Route::middleware('multiauth:api')->resource('board', 'Api\BoardController'); // リクエスト掲示板

    //---　ツイート機能 ---------
    Route::get('tweet/indexTree', 'Api\TweetController@indexTree'); //コメントツリー情報の取得
    Route::middleware('multiauth:api')->post('tweet', 'Api\TweetController@store');//ツイート、コメント登録
    Route::middleware('multiauth:api')->put('tweet/{id}', 'Api\TweetController@update');//ツイート、コメント編集
    Route::get('tweet/{id}', 'Api\TweetController@show');
    Route::middleware('multiauth:api')->delete('tweet/{id}', 'Api\TweetController@destroy');
    Route::get('tweet', 'Api\TweetController@index');//コメント検索取得
    //Route::resource('tweet', 'Api\TweetController');


    //都道府県
	Route::resource('prefectures', 'Api\PrefectureController');

    //チャット
	Route::middleware('multiauth:api')->resource('message', 'Api\MessageController');

    //サイト設定値
    Route::middleware('multiauth:api')->resource('site_config', 'Api\SiteConfigController'); // 設定値

    //広告
    Route::resource('advertisement', 'Api\AdvertisementController');

    //お問合せ
    Route::resource('inquiry', 'Api\InquiryController');

    //FAQ
    Route::resource('faq', 'Api\FaqController');

    //コンテンツ
    Route::get('contents', 'Api\ContentController@index'); // コンテンツ
    Route::get('contents/{id}', 'Api\ContentController@show'); // コンテンツ詳細
    Route::get('content/getMessageContent', 'Api\ContentController@getMessageContent');
    Route::middleware('multiauth:api')->post('content/setMessageContent', 'Api\ContentController@setMessageContent');
    Route::middleware('multiauth:api')->put('content/updateMessage{id}', 'Api\ContentController@updateMessage');
    Route::middleware('multiauth:api')->delete('content/destroyMessage', 'Api\ContentController@destroyMessage');
    Route::middleware('multiauth:api')->post('content/setOfferContent', 'Api\ContentController@setOfferContent');
    Route::middleware('multiauth:api')->post('content/choiceUpdate/{id}', 'Api\ContentController@choiceUpdate');

    //---  イベントカレンダー ---------
    Route::resource('event', 'Api\EventController');

    //---  成長カレンダー ---------
    //各年齢で出来る事
    Route::get('growth/indexGrowthAge', 'Api\GrowthController@indexGrowthAge');//検索
    //子供の成長記録
    Route::middleware('multiauth:api')->post('growth/createGrowthUser', 'Api\GrowthController@createGrowthUser');//登録
    Route::middleware('multiauth:api')->put('growth/updateGrowthUser', 'Api\GrowthController@updateGrowthUser');//更新
    Route::middleware('multiauth:api')->get('growth/indexGrowthUser', 'Api\GrowthController@indexGrowthUser');//検索
    Route::middleware('multiauth:api')->put('growth/updateOrCreateGrowthUser', 'Api\GrowthController@updateOrCreateGrowthUser');

    //指定した子どもの成長記録の取得
    Route::middleware('multiauth:api')->get('growth/indexGrowthUserOne', 'Api\GrowthController@indexGrowthUserOne');//検索
    //指定した子どもの成長記録の取得　ファイルアップ
    Route::middleware('multiauth:api')->post('growth/upFileGrowthUser', 'Api\GrowthController@upFileGrowthUser');//ファイルアップ


    Route::resource('growth', 'Api\GrowthController');

    //クロスワード
    Route::get('content/indexCrossword', 'Api\ContentController@indexCrossword'); // クロスワード検索
    Route::get('content/showCrossword/{id}', 'Api\ContentController@showCrossword'); // クロスワード取得

    //タロット
    Route::get('content/indexTarot', 'Api\ContentController@indexTarot'); // クロスワード検索
    Route::get('content/showTarot/{id}', 'Api\ContentController@showTarot'); // クロスワード取得
    Route::middleware('multiauth:api')->post('content/storeTarotUser', 'Api\ContentController@storeTarotUser'); // 占い結果登録
    Route::get('content/indexTarotUser', 'Api\ContentController@indexTarotUser');

});