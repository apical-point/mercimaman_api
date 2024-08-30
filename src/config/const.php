<?php


// 設定ファイル
return [
    // サイトの設定
    'site' => [
        // ページネータの1ページあたりの件数
        'PER_PAGE' => env('PER_PAGE', 20),

        // 消費税率
        'TAX_RATE' => env('TAX_RATE', 0.1),

        // サイトの情報
        'SITE_TITLE' => env('SITE_TITLE', ''),
        'SITE_NAME' => env('SITE_NAME', ''),
        'SITE_EMAIL' => env('SITE_EMAIL', ''),
        'SITE_TEL' => env('SITE_TEL', ''),
        'SITE_TIME' => env('SITE_TIME', ''),

        // ユーザーパラメータと有効期限
        'PARAM_SALT' => env('PARAM_SALT', '20a33bef2b79'),
        'ENTRY_LIMIT_HOUR' => env('ENTRY_LIMIT_HOUR', 24),
        'PASSWORD_LIMIT_HOUR' => env('PASSWORD_LIMIT_HOUR', 1),

        // メール関連
        'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS', ''),
        'MAIL_FROM_BASIC' => 'merci.maman@tomorrows-inc.com',
        'MAIL_FROM_SUPPORT' => 'support@tomorrows-inc.com',
        'MAIL_FROM_NO' => 'no-reply@tomorrows-inc.com',

        'MAIL_FROM_NAME' => env('MAIL_FROM_NAME', ''),
        'SITE_ADMIN_EMAIL' => env('SITE_ADMIN_EMAIL', ''),
        'MAIL_SEND_FLG' => env('MAIL_SEND_FLG', 0),

        'BASE_URL' => env('BASE_URL', ''),
        'BASE_API_URL' => env('BASE_API_URL', ''),


        'DEMAE_BASE_URL' => env('DEMAE_BASE_URL', ''),

        'PRE_REGISTION_DATE' => '2021-08-31',

        //ストライプ
        'STRIPE_SECRET_KEY' =>env('STRIPE_SECRET_KEY', ''),
        'STRIPE_PUBLIC_KEY' =>env('STRIPE_PUBLIC_KEY', ''),
        'STRIPE_DESCRIPTION_SUBSCRIPTION' => env('STRIPE_DESCRIPTION_SUBSCRIPTION', 'Merci!Maman'),
        'STRIPE_DESCRIPTION_STORE' => env('STRIPE_DESCRIPTION_STORE', 'Merci!Maman 購入情報'),

        'YAMATO_COMPANY_KEY' =>env('YAMATO_COMPANY_KEY', ''),
        'YAMATO_API_KEY' =>env('YAMATO_API_KEY', ''),
        'YAMATO_API_URL_REGIST' =>env('YAMATO_API_URL_REGIST', ''),
        'YAMATO_API_URL_QR' =>env('YAMATO_API_URL_QR', ''),
        'YAMATO_API_URL_CANCEL' =>env('YAMATO_API_URL_CANCEL', ''),

        // FCM プッシュ通知
        'FCM_SENDER_ID' => env('FCM_SENDER_ID'),
        'FCM_PRIVATE_KEY' => env('FCM_PRIVATE_KEY'),

        //イベント登録者手数料  メルママ会員は10% イベント会員は15%
        'USER_CHARGE' => '0.1',
        'USER_EVENT_CHARGE' => '0.15',

    ],
    // 画像の拡張子
    'IMAGE_EXTENSION' => [
        'jpeg', 'png', 'bmp', 'gif', 'svg', 'jpg', 'JPG',
    ],

    // 動画の拡張子
    'MOVIE_EXTENSION' => [
        'avi', 'mov', 'wmv', 'flv', 'mpg', 'mp4',
    ],

    // 画像
    'image' => [
        'resize' => [
            'IMAGE_RESIZE_WIDTH_SIZE' => (int) env('IMAGE_RESIZE_WIDTH_SIZE', 1000),
            'IMAGE_RESIZE_HEIGHT_SIZE' => (int) env('IMAGE_RESIZE_HEIGHT_SIZE', 750)
        ],
    ],


    'line' => [
        'LINE_CLIENT_ID' => env('LINE_CLIENT_ID', null),
        'LINE_CLIENT_SECRET' => env('LINE_CLIENT_SECRET', null),
        'LINE_BASE_API_URL' => env('LINE_BASE_API_URL', null),

        'rich_menu' => [
            'image_size' => [
                '1' => [
                    'width' =>2500,
                    'height' =>1686
                ],
                '2' => [
                    'width' =>2500,
                    'height' =>843
                ]
            ]
        ]
    ],

    'IMAGE_MIME_TYPE' => [
        'image/gif', 'image/png', 'image/jpeg', 'image/bmp', 'image/webp'
    ],

    'order_status' => [
        '1' => '取引中',
        '2' => '発送済',
        '3' => '受取済',
        '4' => '取引完了',
        '5' => '支払い申請中(登録申請)',
        '6' => '支払い申請中(自動申請)',
        '7' => '支払処理中',
        '8' => '支払完了',
        '9' => 'キャンセル',
    ],

    'product_status' => [
        '1' => '下書き',
        '2' => '出品中',
        '3' => '取引中',
        '4' => '発送済',
        '5' => '受取完了',
        '6' => '取引完了',
    ],

    'user_status' => [
        '0' => '仮登録',
        '1' => '本登録',
        '9' => '退会',
    ],

    //ポイントID
    'point_id' => [
        'REGIST_POINT_ID' => '6',       //登録ポイントのID(site_configsテーブルのid)
        'INTRO_POINT_ID' => '7',        //お友達紹介のポイントID(site_configsテーブルのid)
        'INTRO_TO_POINT_ID' => '8',     //お友達紹介のポイントID(site_configsテーブルのid)
        'POINT_EXCHANGE_ID' => '18',    //ポイント交換画面のID(site_configsテーブルのid)
        'LOGIN_POINT_ID' => '9',        //ログインポイントのID(site_configsテーブルのid)
        'ANSWER_POINT_ID' => '13',      //お悩み回答ポイントのID(site_configsテーブルのid)
        'WORRY_POINT_ID' => '14',       //お悩み投稿ポイントのID(site_configsテーブルのid)
        'EXPERIENCE_POINT_ID' => '12',  //体験記投稿ポイントのID(site_configsテーブルのid)
        'CONTENTS_POINT_ID' => '15',    //月、水コンテンツ　ポイントのID(site_configsテーブルのid)
        'THEME_POINT_ID' => '11',       //テーマ投稿　ポイントのID(site_configsテーブルのid)
        'PRESENT_POINT_ID' => '16',     //プレゼント応募　ポイントのID(site_configsテーブルのid)
        'REVIEW_POINT_ID' => '21',      //口コミ投稿　ポイントのID(site_configsテーブルのid)
        'MANAGEMENT_POINT_ID' => '99'   //管理側によるポイント修正のID
    ],

    //こちらは使用しない。データベース「site_config」テーブルの値を参照してCRUD処理をしている。
    'point' => [
        '1' => [
            "info" => "登録ポイント プレゼント",
            "point" => "0",
        ],
        '2' => [
            "info" => "紹介登録ポイント プレゼント",
            "point" => "0",
        ],

        '3' => [
            "info" => "初回出品ポイント 200Pプレゼント",
            "point" => "200",
        ],
        '4' => [
            "info" => "初回購入ポイント 200Pプレゼント",
            "point" => "200",
        ],
        '5' =>[
            "info" => "お友達紹介ポイント プレゼント",
            "point" => "0",
        ],
        '6' => [
            "info" => "ログインポイント 1Ｐプレゼント",
            "point" => "1",
        ],
        '7' => [
            "info" => "商品購入に使用",
            "point" => "0",
        ],
        '8' => [
            "info" => "商品キャンセル時の使用ポイントの返還",
            "point" => "0",
        ],
        '9' => [
            "info" => "管理者ポイント",
            "point" => "0",
        ],
        '10' => [
            "info" => "月、水コンテンツコメント  5Pプレゼント",
            "point" => "5",
        ],
        '11' => [
            "info" => "アンケートテーマ募集に回答 2Pプレゼント",
            "point" => "2",
        ],
        '12' => [
            "info" => "お悩み相談に投稿  2Pプレゼント",
            "point" => "2",
        ],
        '13' => [
            "info" => "体験記投稿  100Pプレゼント",
            "point" => "100",
        ],
        '14' => [
            "info" => "金曜日のプレゼント企画に応募 10P使用",
            "point" => "10",
        ],
        '15' => [
            "info" => "イベントの参加に使用",
            "point" => "0",
        ],
        '16' => [
            "info" => "イベントキャンセルによる使用ポイントの返還",
            "point" => "0",
        ],
        '17' => [
            "info" => "お悩み相談回答  100Pプレゼント",
            "point" => "100",
        ],
    ],


];
