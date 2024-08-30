<?php namespace App\Libraries;

// バリデートの参考ページ
// https://qiita.com/fagai/items/9904409d3703ef6f79a2

// https://readouble.com/laravel/5.5/ja/validation.html
// の「オプションフィールドに対する注意」を読んでください。("required"でないものには"nullable"をつけましょう。)

// 共通チェック配列クラス
class ValidateCheckArray {
	// パスワードのリセット
	public static $resetPassword = [
		'password' => 'required|CustomPassword',
    ];

	// パスワードの更新
	public static $updatePassword = [
		'current_password' => 'required',
		'password' => 'required|CustomPassword',
	];

	// パスワードの更新
	public static $updateEmail = [
		'email' => 'required|email',
	];

	// ユーザー作成
	public static  $createAdminer = [
		'name' => 'required|max:256',
		// 'name_kana' => 'required|max:256|CustomKatakana',
		'password' => 'required|CustomPassword',
		'email'=>'required|email',

    ];

    // パスワード再設定
	public static $sendResetPassword = [
	    'email'=>'required|email',
	];

	// ユーザー更新
	public static  $updateAdminer = [
		'name' => 'required|max:256',
		// 'name_kana' => 'required|max:256|CustomKatakana',
        'email'=>'required|email',
    ];

	// authLogin
	public static  $authLogin  = [
		'password' => 'required',
        'email'=>'required',
	];

	// 画像登録
	public static $lineUpFileImage = [
		'up_file' => 'required',
	];

	// 動画登録
	public static $lineUpFileMovie = [
		'up_file' => 'required',
	];

	// ログインチェック
	public static $loginCheck = [
		'password' => 'required',
		'email' => 'required',
	];

	// メッセージのjson作成
	public static $createMassageJson = [
		'reply_type' => 'required',
		'content' => 'required'
	];

	// メールアドレス
	public static $authEmail = [
		'email' => 'required|email',
    ];

	// 仮登録
	public static $entry = [
		'email' => 'required|email',
        're_email' => 'required|email',
	];

	// 新規登録--顧客
	public static $registClient = [
		'last_name' => 'required|max:60',
		'first_name' => 'required|max:60',
		'last_name_kana' => 'required|max:60|CustomKatakana',
		'first_name_kana' => 'required|max:60|CustomKatakana',
		'zip' => 'required|max:8',
        'prefecture_id' => 'required|numeric',
        'address1' => 'required|max:40',
        'address2' => 'nullable|max:40',
        'address3' => 'nullable|max:40',
        'building' => 'nullable|max:40',
        'tel' => 'required|numeric',
        'password' => 'required|CustomPassword',
        'nickname' => 'required|max:14',
        'condition' => 'required|between:1,3',
        'birthday' => 'required|between:1,3',
        'condition' => 'required|between:1,3',
        'birthday' => 'required|date_format:"Y-m-d"',
        'child_birthday1' => 'nullable|date_format:"Y-m-d"',
        'child_birthday2' => 'nullable|date_format:"Y-m-d"',
        'child_birthday3' => 'nullable|date_format:"Y-m-d"',
        'child_birthday4' => 'nullable|date_format:"Y-m-d"',
        'child_birthday5' => 'nullable|date_format:"Y-m-d"',
		'referral_code' => 'nullable|max:20',
	];

	//海外在住
	public static $registClient2 = [
		'last_name' => 'required|max:60',
		'first_name' => 'required|max:60',
		'last_name_kana' => 'required|max:60|CustomKatakana',
		'first_name_kana' => 'required|max:60|CustomKatakana',
		'zip' => 'required|max:8',
        'prefecture_id' => 'required|numeric',
        'address1' => 'required|max:40',
        'address2' => 'nullable|max:40',
        'address3' => 'nullable|max:40',
        'building' => 'nullable|max:40',
        'tel' => 'required|numeric',
        'password' => 'required|CustomPassword',
        'nickname' => 'required|max:14',
        'condition' => 'required|between:1,3',
        'birthday' => 'required|between:1,3',
        'condition' => 'required|between:1,3',
        'birthday' => 'required|date_format:"Y-m-d"',
        'child_birthday1' => 'nullable|date_format:"Y-m-d"',
        'child_birthday2' => 'nullable|date_format:"Y-m-d"',
        'child_birthday3' => 'nullable|date_format:"Y-m-d"',
        'child_birthday4' => 'nullable|date_format:"Y-m-d"',
        'child_birthday5' => 'nullable|date_format:"Y-m-d"',
		'referral_code' => 'nullable|max:20',
	];

	// アップデート--顧客
	public static $updateClient = [
        'last_name' => 'required|max:60',
		'first_name' => 'required|max:60',
		'last_name_kana' => 'required|max:60|CustomKatakana',
		'first_name_kana' => 'required|max:60|CustomKatakana',
		'zip' => 'required|max:8',
        'prefecture_id' => 'required|numeric',
        'address1' => 'required|max:40',
        'address2' => 'nullable|max:40',
        'address3' => 'nullable|max:40',
        'building' => 'nullable|max:40',
        'tel' => 'required|numeric',
        'nickname' => 'required|max:14',
        'condition' => 'required|between:1,3',
        'birthday' => 'required|between:1,3',
        'condition' => 'required|between:1,3',
        'birthday' => 'required|date_format:"Y-m-d"',
        'child_birthday1' => 'nullable|date_format:"Y-m-d"',
        'child_birthday2' => 'nullable|date_format:"Y-m-d"',
        'child_birthday3' => 'nullable|date_format:"Y-m-d"',
        'child_birthday4' => 'nullable|date_format:"Y-m-d"',
        'child_birthday5' => 'nullable|date_format:"Y-m-d"',
		'referral_code' => 'nullable|max:20',
	];

	//海外在住の人
	public static $updateClient2 = [
        'last_name' => 'required|max:60',
		'first_name' => 'required|max:60',
		'last_name_kana' => 'required|max:60|CustomKatakana',
		'first_name_kana' => 'required|max:60|CustomKatakana',
        'prefecture_id' => 'required|numeric',
        'tel' => 'required|numeric',
        'nickname' => 'required|max:14',
        'condition' => 'required|between:1,3',
        'birthday' => 'required|between:1,3',
        'condition' => 'required|between:1,3',
        'birthday' => 'required|date_format:"Y-m-d"',
        'child_birthday1' => 'nullable|date_format:"Y-m-d"',
        'child_birthday2' => 'nullable|date_format:"Y-m-d"',
        'child_birthday3' => 'nullable|date_format:"Y-m-d"',
        'child_birthday4' => 'nullable|date_format:"Y-m-d"',
        'child_birthday5' => 'nullable|date_format:"Y-m-d"',
		'referral_code' => 'nullable|max:20',
	];


    // 新規登録--プロフィール
	public static $registProfile = [
		'image_id' => 'required|numeric|min:1',
        'chat' => 'required|between:0,1',
//		'taste' => 'required',
        'mother_interest' => 'required',
        'introduction' => 'required|max:250',
	];

    // 新規登録--プロフィール
	public static $updateProfile = [
		'image_id' => 'required|numeric|min:1',
        'chat' => 'required|between:0,1',
//		'taste' => 'required',
        'mother_interest' => 'required',
        'introduction' => 'required|max:250',
	];

    // 退会
	public static $withdrawal = [
		'withdrawal' => 'required',
    ];

    // 銀行口座
	public static $registBank = [
        'bank_code' => 'required|numeric|digits:4',
        'bank_type' => 'required|between:1,2',
        'bank_branch_code' => 'required|numeric|digits:3',
        'bank_number' => 'required|numeric|digits:7',
		'bank_name' => 'required|max:20|CustomKatakana',
	];

	// 商品登録
	public static $registProduct = [
		'product_name' => 'required|max:20',
		'product_category1_id' => 'required',
        'product_category2_id' => 'required',
        'detail' => 'required',
        'status' => 'required',
        'user_id' => 'required',
        'brand' => 'required',
        'condition' => 'required',
        'taste' => 'required',
        'size' => 'nullable|max:40',
		'price' => 'required|numeric',
		'shipping_charges' => 'required',
		'shipping_method' => 'required|between:0,12',
		'shipping_day' => 'required|numeric',
		//'shipping_area' => 'required',
        'handing' => 'required|between:0,1',
	];

	// 商品編集
    public static $updateProduct = [
        'product_name' => 'required|max:40',
		'product_category1_id' => 'required',
        'product_category2_id' => 'required',
        'detail' => 'required',
        'status' => 'required',
        'user_id' => 'required',
        'brand' => 'required',
        'condition' => 'required',
        'taste' => 'required',
		'price' => 'required|numeric',
        'size' => 'nullable|max:40',
		'shipping_charges' => 'required',
		'shipping_method' => 'required|between:0,12',
		'shipping_day' => 'required|numeric',
		//'shipping_area' => 'required',
        'handing' => 'required|between:0,1',
	];

	public static $cartItem = [
		'product_id' => 'required|numeric',
		'quantity' => 'required|numeric|min:1',
		'options' => 'nullable|array',
		// 'options.*' => 'required|numeric',
    ];

    // 注文データ
    public static $createOrder = [
        'user_id' => 'required',
        'product_id' => 'required',
        'point' => 'required|numeric',
    ];

	// 注文データ
	public static $cart = [
        'cart' => 'required|array',
        'cart.*' => 'nullable|array',
	];


	// カテゴリ作成
	public static $createCategory = [
		'product_category_name' => 'required',
		'parentid' => 'nullable|numeric',
		'c_flg' => 'nullable|numeric',
	];

	// カテゴリ更新
	public static $updateCategory = [
		'product_category_name' => 'required',
		'parentid' => 'nullable|numeric',
		'v_order' => 'nullable|numeric',
		'c_flg' => 'nullable|numeric',
	];

    // 商品メッセージの作成
	public static $createProductMassage = [
		'message' => 'required|max:512'
	];

	// メッセージ
	public static $messageStore = [
	    'message' => 'required|max:1000',
	    'user_from_id' => 'required',
	    'user_to_id' => 'required',
	];
	public static $messageStoreImg = [
	    'message' => 'max:1000',
	    'user_from_id' => 'required',
	    'user_to_id' => 'required',
	];

    //配送料サイズ
    public static $mstDeliverySizeStore = [
        'name' => 'required|max:256',
        'size' => 'required|numeric',
        'weight' => 'required|numeric',
    ];

    //配送料登録
    public static $mstDeliveryChargeStore = [
        'area_id' => 'required|numeric',
        'size_id' => 'required|numeric',
        'price' => 'required|numeric',
    ];

    //各種設定
    public static $updateSiteConfig = [
    ];

    // ストライプID
	public static $setStripeId = [
		'stripe_id' => 'required',
    ];

    // 月・店舗別検索機能付き売り上げリスト
	public static $salesList = [
		'order_year' => 'nullable|numeric',
		'order_month' => 'nullable|numeric',
    ];
	public static $salesDetails = [
		'shop_id' => 'required|numeric',
		'order_year' => 'required|numeric',
		'order_month' => 'required|numeric',
    ];

    public static $orderStore = [
        'shop_id' => 'required|numeric',
        'user_id' => 'required|numeric',
        'subtotal_price' => 'required|numeric',
		'delivery_charge' => 'required|numeric',
        'tax_price' => 'required|numeric',
        'total_price' => 'required|numeric',
        'system_charge' => 'required|numeric',
        'delivery_date_start' => 'required|date_format:"Y-m-d"',
    ];

    public static $order1Update = [
        'seller_evaluation' => 'required|numeric',
    ];

    public static $order2Update = [
        '	buyer_evaluation' => 'required|numeric',
    ];

    public static $orderPostage = [
        'postage' => 'required|numeric',
    ];


    // コンテンツ
	public static $content = [
	    'themedate' => 'required|date_format:"Y-m-d"',
	    'theme' => 'required|max:40',
	    'choicestheme' => 'required|max:40',
	    'answer1' => 'required|max:20',
	    'answer2' => 'required|max:20',
	    'present' => 'required|max:40',
        'presentdetail' => 'required|max:200',
        'election' => 'required|numeric|min:1',
	    'company_name' => 'required|max:40',
        'company_url' => 'nullable|max:256',
	];

    // コンテンツメッセージ
	public static $contentMessageStore = [
	    'content_id' => 'required',
	    'type' => 'required',
        'user_id' => 'required',
	    'message' => 'required|max:1000',
	    'open_flg' => 'required',
	];

    // コンテンツメッセージ
	public static $contentOfferStore = [
	    'type' => 'required',
        'user_id' => 'required',
	    'theme' => 'required|max:40',
	];

    // お知らせ
	public static $newsCreate = [
		'title' => 'required|max:256',
		'status' => 'required|numeric|between:0,1',
		'open_date' => 'required|date_format:"Y-m-d"',
		'detail' => 'required',
    ];

	// よくあるご質問
	public static $faqCreate = [
		'title' => 'required|max:256',
		'sort' => 'required|numeric',
		'detail' => 'required',
    ];

	// 問い合わせ
	public static $inquiryCreate = [
		'detail' => 'required',
		'email' => 'required|email',
		'title' => 'required',
        'inquiry_flg' => 'required',
        'demand_flg' => 'required',
    ];

	// 問い合わせ
	public static $inquiryUpdate = [
		'reply_mail_text' => 'required',
    ];

	//広告出稿
	public static $inquiryAdvertisementCreate = [
//	    'detail' => 'required',
	    'email' => 'required|email',
	    'name' => 'required',
	    'company_name' => 'required',
	    'budget' => 'required',
	    'inquiry_flg' => 'required',
	    'demand_flg' => 'required',
	    'privacy' => 'required',
	];


    // 広告出稿
    public static $advertisement = [
        'type' => 'required',
    ];

    // 広告出稿
    public static $advertisementComapny = [
		'advertisement_name' => 'required|max:19',
        'detail' => 'nullable|max:54',
        'type' => 'required',
        'company' => 'nullable|max:40',
        'up_main_file' => 'required',
        'term' => 'required',
    ];

	public static $advertisementComapnyUpdate = [
        'advertisement_name' => 'required|max:19',
        'detail' => 'nullable|max:54',
        'type' => 'required',
        'company' => 'nullable|max:40',
        'term' => 'required',
    ];
    // 広告出稿
    public static $advertisementScript = [
        'advertisement_name' => 'required|max:19',
        'detail' => 'nullable|max:54',
        'type' => 'required',
        'company' => 'required|max:40',
        'script' => 'required',
    ];

    // ポイント
    public static $pointCreate = [
        'user_id' => 'required',
        'point_type' => 'required',
        'point_detail' => 'required',
        'point' => 'required|numeric',
        'point_date' => 'required|date_format:"Y-m-d"',
    ];

    // ポイント削除
    public static $pointDelete = [
        'user_id' => 'required',
        'point_detail' => 'required',
        'point' => 'required|numeric',
        'point_date' => 'required|date_format:"Y-m-d"',
    ];

    // お知らせ
    public static $newCreate = [
        'user_id' => 'required',
        'title' => 'required',
        'detail' => 'required',
        'status' => 'required',
        'news_flg' => 'required',
        'open_date' => 'required|date_format:"Y-m-d"',
        'open_flg' => 'required',
    ];

	// お知らせ
    public static $newCreate_public = [
        'title' => 'required',
        'detail' => 'required',
        'status' => 'required',
        'news_flg' => 'required',
        'open_date' => 'required|date_format:"Y-m-d"',
        'open_flg' => 'required',
    ];

    // お知らせ
    public static $newsUpdate = [
        'status' => 'required',
    ];

	// お知らせ
    public static $newsUpdate_public = [
        'title' => 'required',
        'detail' => 'required',
        'status' => 'required',
        'news_flg' => 'required',
        'open_date' => 'required|date_format:"Y-m-d"',
        'open_flg' => 'required',
    ];

    //リクエスト掲示板
    public static $boardRequest = [
        'user_id' => 'required',
        'detail' => 'required',
     ];

    //体験掲示板　要望
    public static $boardExpRequest = [
        'user_id' => 'required',
        'detail' => 'required',
    ];

    //体験掲示板　体験記
    public static $boardExpCommentRequest = [
        'user_id' => 'required',
//        'title' => 'required',
        'detail' => 'required',
    ];

    //ツイート機能
    public static $tweet = [
        'user_id' => 'required',
        'tweet' => 'required',
    ];

    public static $tweet_rep = [
        'user_id' => 'required',
        'tweet' => 'required',
        'parent_id' => 'required',
    ];

    public static $growthAge = [
        'age_no' => 'required',
        'name' => 'required',
    ];

    //クロスワード、
    public static $crossword = [
        'post_date' => 'required',
        'answer' => 'required',
        'xq1' => 'required',
        'xq2' => 'required',
        'xq3' => 'required',
        'xq4' => 'required',
        'yq1' => 'required',
        'yq2' => 'required',
        'yq3' => 'required',
        'yq4' => 'required',
    ];

    //タロット
    public static $tarot = [
        'post_date' => 'required',
        'card_result1' => 'required',
        'card_result2' => 'required',
        'card_result3' => 'required',
        'card_result4' => 'required',
        'card1' => 'required',
        'card2' => 'required',
        'card3' => 'required',
        'card4' => 'required',
    ];

    //参加イベント
    public static $userEvent = [
        'user_id' => 'required|numeric',
        'event_name' => 'required',
        'event_date' => 'required',
        //'event_time' => 'required',
        'place' => 'required',
        'event_price' => 'required|numeric',
        'member_cnt' => 'required|numeric',
        'host_name' => 'required',
        'contact' => 'required',
        'topic1' => 'max:5',
        'topic2' => 'max:5',
        'agree' => 'required',
    ];

}
