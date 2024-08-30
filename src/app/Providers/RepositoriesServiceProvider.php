<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoriesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        \App::bind('App\Repositories\AdminerRepositoryInterface', 'App\Repositories\Eloquent\AdminerRepository'); // 管理者
        \App::bind('App\Repositories\UserRepositoryInterface', 'App\Repositories\Eloquent\UserRepository'); // ユーザー
        \App::bind('App\Repositories\UserDetailRepositoryInterface', 'App\Repositories\Eloquent\UserDetailRepository'); // ユーザー詳細
        \App::bind('App\Repositories\UserProfileRepositoryInterface', 'App\Repositories\Eloquent\UserProfileRepository'); // ユーザープロフィール
        \App::bind('App\Repositories\UserFavoriteRepositoryInterface', 'App\Repositories\Eloquent\UserFavoriteRepository'); // ユーザーお気に入り

        \App::bind('App\Repositories\PointRepositoryInterface', 'App\Repositories\Eloquent\PointRepository'); // ユーザーポイント

        \App::bind('App\Repositories\ProductRepositoryInterface', 'App\Repositories\Eloquent\ProductRepository'); // 商品
        \App::bind('App\Repositories\ProductCategoryRepositoryInterface', 'App\Repositories\Eloquent\ProductCategoryRepository'); // 商品カテゴリ
        \App::bind('App\Repositories\ProductMessageRepositoryInterface', 'App\Repositories\Eloquent\ProductMessageRepository'); // 商品メッセージ
        \App::bind('App\Repositories\ProductBrandRepositoryInterface', 'App\Repositories\Eloquent\ProductBrandRepository'); // 商品ブランド

        \App::bind('App\Repositories\OrderRepositoryInterface', 'App\Repositories\Eloquent\OrderRepository'); // 注文
        \App::bind('App\Repositories\OrderDetailRepositoryInterface', 'App\Repositories\Eloquent\OrderDetailRepository'); // 注文詳細
        \App::bind('App\Repositories\OrderPaymentRepositoryInterface', 'App\Repositories\Eloquent\OrderPaymentRepository'); // 注文詳細

        \App::bind('App\Repositories\UpFileRepositoryInterface', 'App\Repositories\Eloquent\UpFileRepository'); // 投稿ファイル

        \App::bind('App\Repositories\PrefectureRepositoryInterface', 'App\Repositories\Eloquent\PrefectureRepository'); // 都道府県

        \App::bind('App\Repositories\MessageRepositoryInterface', 'App\Repositories\Eloquent\MessageRepository'); // メッセージ
        \App::bind('App\Repositories\SiteConfigRepositoryInterface', 'App\Repositories\Eloquent\SiteConfigRepository'); //各種設定

        \App::bind('App\Repositories\InquiryRepositoryInterface', 'App\Repositories\Eloquent\InquiryRepository'); //問い合わせ
        \App::bind('App\Repositories\NewsRepositoryInterface', 'App\Repositories\Eloquent\NewsRepository'); //お知らせ
        \App::bind('App\Repositories\FaqRepositoryInterface', 'App\Repositories\Eloquent\FaqRepository'); //よくある質問
        \App::bind('App\Repositories\AdvertisementRepositoryInterface', 'App\Repositories\Eloquent\AdvertisementRepository'); //広告

        \App::bind('App\Repositories\ContentRepositoryInterface', 'App\Repositories\Eloquent\ContentRepository'); //コンテンツ
        \App::bind('App\Repositories\ContentMessageRepositoryInterface', 'App\Repositories\Eloquent\ContentMessageRepository'); //コンテンツメッセージ
        \App::bind('App\Repositories\ContentOfferRepositoryInterface', 'App\Repositories\Eloquent\ContentOfferRepository'); //コンテンツテーマ募集

        \App::bind('App\Repositories\MailDeliveryRepositoryInterface', 'App\Repositories\Eloquent\MailDeliveryRepository'); //メール配信
        \App::bind('App\Repositories\MailDeliveryDetailRepositoryInterface', 'App\Repositories\Eloquent\MailDeliveryDetailRepository'); //メール配信詳細
        \App::bind('App\Repositories\MailSigRepositoryInterface', 'App\Repositories\Eloquent\MailSigRepository'); //署名
        \App::bind('App\Repositories\MailTemplateRepositoryInterface', 'App\Repositories\Eloquent\MailTemplateRepository'); //メールテンプレート

        \App::bind('App\Repositories\BoardRepositoryInterface', 'App\Repositories\Eloquent\BoardRepository'); //掲示板

        \App::bind('App\Repositories\TweetRepositoryInterface', 'App\Repositories\Eloquent\TweetRepository'); //ツイート機能

        \App::bind('App\Repositories\EventRepositoryInterface', 'App\Repositories\Eloquent\EventRepository'); //イベントカレンダー
        \App::bind('App\Repositories\GrowthRepositoryInterface', 'App\Repositories\Eloquent\GrowthRepository'); //成長カレンダー

        \App::bind('App\Repositories\UserEventRepositoryInterface', 'App\Repositories\Eloquent\UserEventRepository'); //ユーザー作成イベント

        \App::bind('App\Repositories\TopSlideDataRepositoryInterface', 'App\Repositories\Eloquent\TopSlideDataRepository'); //TOPスライダー画像とコンテンツ

    }
}
