## 概要
laravel6で管理者テーブルとユーザーテーブルが分かれているものです。(2020-02-18に再度入れなおしました。)

https://github.com/sfelix-martins/passport-multiauth
を参考にマルチテーブルを入れました。

## 新規プロジェクト開始時に行うこと(行わないと動かない)
```
1 .envの作成
2 composer install
3 php artisan optimize:clear
4 php artisan migrate
5 php artisan passport:install
6 php artisan vendor:publish --provider="SMartins\PassportMultiauth\Providers\MultiauthServiceProvider"
7 php artisan storage:link
8 php artisan db:seed
```

7番のコマンドについて  
ストレージのリンクコマンドです。
コピーしてプロジェクトを開始する場合は、「/public/storageのシンボリックを削除」してから7番のコマンドを実行すること。(こうしないとうまくいかない。理由は、よくわからない。。。)  



## 認証について
/oauth/token  
がlaravel標準の認証のurlです。以下のjsonをpostすると{access_token}が得られる。  
client_idやclient_secretはoauth_clientsテーブルのname='Laravel Password Grant Client'のものを入れること。

ユーザー認証の例  
{  
    "username":"test+user0001@test.com",  
    "password":"111111!l",  
    "grant_type" : "password",  
    "client_id": "2",  
    "client_secret" : "aOlc4oCQi4h1a5YQOMJQkfnS28DilbvDQo0C60w0",  
    "provider" : "users"  
}

管理者認証の例  
{  
    "username":"test+adminer0001@test.com",  
    "password":"111111!l",  
    "grant_type" : "password",  
    "client_id": "2",  
    "client_secret" : "aOlc4oCQi4h1a5YQOMJQkfnS28DilbvDQo0C60w0",  
    "provider" : "admins"  
}


## トークンの渡し方
ヘッダーに以下のを追加してリクエストを投げる。  
Authorization: Bearer {access_token}


## route.phpについて
ユーザーのみのアクセスの例  
Route::get('url', 'Api\AuthController@test')->middleware('multiauth:api');  

管理者のみのアクセスの例  
Route::get('url', 'Api\AuthController@test')->middleware('multiauth:admin');  


## 課題
自分の時間でお試しで作ったため、細かい機能は入れることができませんでした。  
今後は以下のものを入れた方がいいです。  
・「ログイン」や「マイデータ更新」などはUserコントローラーではなく、Authコントローラーとしてやった方がいい。  
・今後のため、機能を分割できるように(マイクロサービス??)意識した方がいい。  
具体的には  
・画像などはポリモーフィックリレーションでいいですが、カテゴリなどはポリモーフィックリレーションにしないで、  
productテーブルとproduct_categoryテーブル  
shopテーブルとshop_categoryテーブル 
のようにする。


## ファイルの保存などについて
ファイルの保存はポリモーフィックリレーションを用いるべきだと思います。mileではそのようにされています。  
また、ファイルの登録と情報の登録は分けたほうがいいです。  
例えば商品と商品画像を登録する際には、商品の情報をいったん登録してから、その後画像を登録するようにする  
もしくは
ファイルを先に保存してからそのファイル情報と一緒に商品画像も保存する  
ようなもの

## キャッシュクリア
php artisan cache:clear  
php artisan config:clear  
php artisan route:clear  
php artisan view:clear  
composer dump-autoload  
php artisan clear-compiled  
php artisan optimize  
php artisan config:cache
