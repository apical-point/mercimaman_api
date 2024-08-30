<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | such as the size rules. Feel free to tweak each of these messages.
    |
    */

    "accepted"         => ":attributeが確認されていません。",
    "active_url"       => ":attributeは無効なURLです。",
    "after"            => ":attributeは:dateより後の日付でなければなりません。",
    "alpha"            => ":attributeにはアルファベット以外使用できません。",
    "alpha_dash"       => ":attributeにはアルファベット、数字、ハイフン、アンダーバー以外使用できません。",
    "alpha_comma"       => ":attributeにはアルファベット、数字、ハイフン、アンダーバー、カンマ以外使用できません。",
    "alpha_num"        => ":attributeにはアルファベット、数字以外使用できません。",
    "alpha_space"      => ":attributeにはアルファベット、空白以外使用できません。",

    // 自作バリデートの文言
    "custom_password"      => ":attributeは半角アルファベット・半角数字をそれぞれ1種類以上含む8文字以上64文字以下で設定してください。",
    "custom_zip"      => ":attributeは正しく入力してください。",
    'custom_katakana' => ':attributeはカナで入力してください。',
    'custom_tel' => ':attributeは正しい値で入力してください。',
    'custom_date_time' => ':attributeは正しい値で入力してください。',
    'custom_past_date' => '過去の日にちを入れてください。',


    "before"           => ":attributeは:dateより前の日付でなければなりません。",
    "between"          => array(
        "numeric" => ":attributeは:min～:maxの範囲である必要があります。",
        "file"    => ":attributeのファイルサイズは:min～:maxキロバイトの範囲である必要があります。",
        "string"  => ":attributeの長さは:min～:max文字の範囲である必要があります。",
    ),
    "confirmed"        => ":attributeは確認欄と一致しませんでした。",
    "date"             => ":attributeは正しい日付ではありません。",
    "date_format"      => ":attributeは:format形式ではありません。",
    "different"        => ":attributeと:otherは異なる必要があります。",
    "digits"           => ":attributeは:digits桁である必要があります。",
    "digits_between"   => ":attributeは:min～:max桁の範囲である必要があります。",
    "email"            => ":attributeは正しいメールアドレスではありません。",
    "exists"           => "選択された:attributeは存在しませんでした。",
    "image"            => ":attributeは画像ファイルである必要があります。",
    "in"               => "選択された:attributeは正しくありません。",
    "integer"          => ":attributeは整数である必要があります。",
    "ip"               => ":attributeは正しいIPアドレスではありません。",
    "max"              => array(
        "numeric" => ":attributeは:max以下である必要があります。",
        "file"    => ":attributeのファイルサイズは:maxキロバイト以下である必要があります。",
        "string"  => ":attributeの長さは:max文字以下である必要があります。",
    ),
    "mimes"            => ":attributeのファイル種別は:valuesである必要があります。",
    "min"              => array(
        "numeric" => ":attributeは:min以上である必要があります。",
        "file"    => ":attributeのファイルサイズは:minキロバイト以上である必要があります。",
        "string"  => ":attributeの長さは :min文字以上である必要があります。",
    ),
    "not_in"           => "選択された:attributeは正しくありません。",
    "numeric"          => ":attributeは数値である必要があります。",
    "reserved_word"    => "この:attributeを使用することはできません。",
    "regex"            => ":attributeの形式は正しくありません。",
    "required"         => ":attributeは必須です。",
    "required_if"      => ":otherが:valueである場合、:attributeは必須です。",
    "required_with"    => ":valuesが指定されている場合、:attributeは必須です。",
    "required_without" => ":valuesが指定されていない場合、:attributeは必須です。",
    "same"             => ":attributeと:otherが一致しません。",
    "size"             => array(
        "numeric" => ":attributeは:sizeである必要があります。",
        "file"    => ":attributeのファイルサイズは:sizeキロバイトである必要があります。",
        "string"  => ":attributeの長さは:size文字である必要があります。",
    ),
    "unique"           => ":attributeはすでに使われています。",
    "url"              => ":attributeは正しいURL形式ではありません。",

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        // スタート価格
        'start_price' => [
            'max' => ':attributeは:max万円以下である必要があります。',
            'min' => ':attributeは:min万円以上である必要があります。',
        ],

        // 最低落札価格
        'lowest_price' => [
            'max' => ':attributeは:max万円以下である必要があります。',
            'min' => ':attributeは:min万円以上である必要があります。',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'member_id' => 'メンバーid',
        'remarks' => '備考',


        // -------------- メンバーテーブル --------------
        // 認証--共通
        'email' => 'メールアドレス',
        're_email' => 'メールアドレス(確認)',
        'password' => 'パスワード',
        'current_password' => 'パスワード',

        // メール
        'title' => 'タイトル',
        'tittle' => 'タイトル',
        'body' => '本文',


        'answer_category_id' => 'グループ',
        'key_word' => 'キーワード',

        'up_file' => 'ファイル',

        'reply_type' => 'メッセージタイプ',
        'content' => 'コンテンツ',

        'last_name' => '姓',
        'first_name' => '名',
        'last_name_kana' => '姓カナ',
        'first_name_kana' => '名カナ',

        'text' => 'テキスト',
        'uri' => 'uri',
        'label' => 'ラベル',

        'chat_bar_text' => 'チャットバーテキスト',
        'management_name' => '管理名',

        'address' => '住所',
        'tel' => '電話番号',
        'zip' => '郵便番号',

        'user_type' => 'ユーザータイプ',
        'prefecture_id' => '都道府県',
        'price' => '値段',

        'product_category_id' => '商品カテゴリー',
        'up_main_file' => 'メイン画像',
        'vire_flg' => '公開/非公開',
		'detail' => '詳細',

        'num' => '個数',
        'color_id' => 'カラー',
        'size_id' => 'サイズ',

        'address1' => '住所',
        'address2' => '建物名',

        // 約款
        'agreement' => '利用規約への同意',

        //配送料
        'size'=>'大きさ(CM)',
        'weight'=>'重さ(KG)',

        //注文データ
        'subtotal_price'=>'商品代金',
        'total_price'=>'支払い金額',
        'delivery_date_start'=>'到着予定日',
        'order_date'=>'注文確定日',

        'receiver_last_name' => 'お届け先姓',
        'receiver_first_name' => 'お届け先名',
        'receiver_address' => 'お届け先住所',
        'receiver_tel' => 'お届け先電話番号',
        'receiver_zip' => 'お届け先郵便番号',
        'receiver_prefecture_id' => 'お届け先都道府県',
        'receiver_building' => 'お届け先建物',
        'prefecture_pid' => '都道府県',

        'product_category_name' => 'ジャンル',

        'themedate' => '今週のテーマ掲載日',
        'theme' => 'テーマ',
        'choicestheme' => '二択テーマ',

        'open_date' => '公開日',
        'type' => '種類',
        'term' => '期間',
        'company' => '会社名',
        'advertisement_name' => '商品名',

        'script' => 'スクリプト',

        'sort' => '順番',

        //プロフィール
        'nickname' => 'ニックネーム',
        'birthday' => '生年月日',

        'condition' => '現在の状況',

        'child_birthday1' => 'お子様の誕生日',
        'child_gender1' => '性別',

        'child_birthday2' => 'お子様の誕生日',
        'child_gender2' => '性別',

        'child_birthday3' => 'お子様の誕生日',
        'child_gender3' => '性別',

        'child_birthday4' => 'お子様の誕生日',
        'child_gender4' => '性別',

        'child_birthday5' => 'お子様の誕生日',
        'child_gender5' => '性別',

        'introduction' => '自己紹介',
        'image_id' => '画像選択',
        'mother_interest' => 'ママの興味',
        'taste' => '好きなテイスト',
        'withdrawal' => '退会理由',

        //銀行
        'bank_code' => '金融機関コード',
        'bank_type' => '口座種別',
        'bank_branch_code' => '支店コード',
        'bank_number' => '口座番号',
        'bank_name' => 'セイメイ',

        //商品
        'product_name' => '商品名',
		'product_category1_id' => 'ジャンル１',
        'product_category2_id' => 'ジャンル２',
        'brand' => 'ブランド',
        'condition' => '状態',
		'price' => '価格',
		'shipping_charges' => '発送料の負担',
		'shipping_method' => '発送方法',
		'shipping_day' => '	発送日の目安',
		'shipping_area' => '	発送元の地域',
        'handing' => '手渡しの可否',

        'point' => 'ポイント',
        'point_detail' => 'ポイント理由',
        'shipping_day' => '発送日の目安',

        'answer1' => '回答１',
        'answer2' => '回答２',
        'presentdetail' => '商品説明',
        'company_name' => '会社名',
        'election' => 'プレゼント人数',
        'present' => 'プレゼント',

        'postage' => '匿名配送 送料',

        'event_name' => 'イベント名',
        'event_date' => 'イベント日',
        'event_time' => 'イベント時間',
        'member_cnt' => '募集人数',
        'place' => 'イベント開催場所',
        'event_price' => '参加費',

        'host_name' => '主催者・主催団体',
        'contact' => 'お問い合わせ先 ',
        'topic1' => '一つ目のトピック',
        'topic2' => '二つ目のトピック',
        'topic3' => '三つ目のトピック',

        'agree' => '利用規約および特定商取引法への同意',

    ],
);
