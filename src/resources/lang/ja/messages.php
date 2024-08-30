<?php


// 使いかた
// __('messages.invalid_url')


return [

    // 基本失敗メッセージ
    'faild_create' => '作成に失敗しました。',
    'faild_update' => '更新に失敗しました。',
    'faild_delete' => '削除に失敗しました。',
    'faild_cancel' => 'キャンセルに失敗しました。',
    'faild' => '失敗しました。',

    // 基本成功メッセージ
    'success' => '成功しました。',
    'success_create' => '作成しました。',
    'success_update' => '更新しました。',
    'success_delete' => '削除しました。',
    'success_cancel' => 'キャンセルしました。',


    'faild_already_registered' => 'すでに登録済みです。',
    'faild_validate' => '入力に間違いがあります。',
    'not_found' => '見つかりませんでした。',
    'not_data' => 'データがありません。',


    //
    'success_regist' => '登録しました。',


    // エントリーメッセージ
    'duplicate_regist_email' => 'このアドレスは登録済みです。',
    'expired_entry' => '有効期限切れです。再度登録をお願いいたします。',
    'awaiting_approval' => '承認待ちです。',
    'invalid_url' => '不正なURLが入力されました。',
    're_email_regist' => 'メールアドレスと確認用メールアドレスが一致しません。',



    // バリデートメッセージ
    'not_jasson_reply1' => 'json形式ではありません。',
    'not_jasson_reply2' => 'json形式ではありません。',
    'not_jasson_reply3' => 'json形式ではありません。',

    'duplication_answer_category_name' => 'グループ名が重複しています。',
    'duplication_answer_key_word' => 'キーワードがグループ内で重複しています。',
    'not_found_anser_category' => 'グループが見つかりませんでした。',
    'can_not_delete_for_answer' => '自動応答が紐づいているため、削除できません。',
    'duplication_scenario_name' => 'シナリオ名が重複しています。',

    'not_image_file' => '画像ファイルではありません。',
    'not_movie_file' => '動画ファイルではありません。',

    'up_file_is_required' => 'ファイルは必須です。',
    'text_message_is_required' => 'テキストは必須です。',


    'required_line_carousel_label' => 'ラベルは必須です。',
    'required_line_carousel_uri' => 'uriは必須です。',

    'not_delete_myself' => '自分自身は削除できません',

    'not_entry' => 'エントリーされていません',

    'the_temporary_registration_deadline_has_passed' => '仮登録期限が過ぎています',

    'faild_regist' => '登録に失敗しました。',

    'faild_withdrawal' => '退会処理に失敗しました。',
    'success_withdrawal' => '退会処理が完了しました。ご利用いただきありがとうございました。',
    'faild_withdrawal_limit_cancel_time' => 'キャンペーンにお申込みいただいたお客様は、退会処理にあたり、規定回数のご購入をいただく必要があります。詳しくはお問い合わせください。',

    'duplication_email' => 'メールアドレスが重複しています。',

    'success_entry' => "仮登録を受け付けました。\nご入力いただいたメールアドレスに本登録用urlを送信しました。\nメールのご確認をお願いいたします。",
    'success_regist_entry' => "登録が完了いたしました。\nお手続きいただき、ありがとうございます。\nそれでは、Merci,Maman!をお楽しみください♪",

    'success_update_User' => '会員情報の更新を行いました。',

    'success_order_create' => '購入が完了しました。出品者の発送通知をお待ちください。',

    'success_update_my_data' => 'アカウント編集を行いました。',

    'is_not_now_password' => '現在のパスワードが正しくありません。',

    'does_not_match_confirm_password' => '確認パスワードが正しくありません。',
    'success_update_password' => 'パスワードの変更が完了しました。',

    'success_send_reset_password_mail' => "ご入力いただいたメールアドレスにパスワードリセット用URLを送信しました。\nメールのご確認をお願いいたします。",
    'failed_reset_password' => 'パスワードリセットに失敗しました。',
    'the_reset_password_deadline_has_passed' => '仮登録期限が過ぎています',
    'success_reset_password' => 'パスワードのリセットに成功しました。新しいパスワードでログインが可能です。',

    'success_send_change_email_mail' => "メールアドレス変更の手続きはまだ終了していません。\nご入力いただいたメールアドレスにメールアドレス変更完了用URLを送信しました。\nメールのご確認をお願いいたします。",
    'failed_change_email' => 'メールアドレスの変更に失敗しました。',
    'success_update_email' => 'メールアドレスの変更が完了しました。',

    // Stripe
    'failed_charge' => '決済に失敗しました。',
    'success_charge' => '決済が完了しました。',
    'failed_stripe' => 'クレジットカード決済会社に問題が発生しています。',
    'stripe_charge_expired_for_capture' => '与信期限切れです。',
    'stripe_incorrect_cvc' => 'セキュリティコード(CVC)が不正です。',
    'stripe_expired_card' => 'カードの有効期限切れです。',
    'stripe_incorrect_number' => 'カード番号が間違っています。',
    'stripe_invalid_cvc' => 'セキュリティコード(CVC)が無効です。',
    'stripe_invalid_expiry_month' => 'カードの有効期限が間違っています。',
    'stripe_invalid_expiry_year' => 'カードの有効期限が間違っています。',
    'stripe_invalid_number' => 'カード番号が間違っています。',
    'stripe_card_declined' => 'カードが拒否されました。',
    'stripe_processing_error' => '処理できませんでした。カードをご確認ください。',

    'shop_data_not_found' => 'ストアに設定された店舗情報が見つかりませんでした。',
    'delivery_charge_not_found' => '配送料の算出ができません。カートをクリアしてください。詳しくはお問い合わせください。',

    'success_update_plan' => 'プランの変更が完了しました。',
    'success_restart_plan' => 'プランを再開しました。',

    'content_date_error' => '既に同じ日付が登録されています。',

    'birthday_gender_error' => '生年月日と性別を指定してくだい。',
    'keyword_error' => '選択してください。',

    'withdrawal_order_error' => '取引中の商品があるため、退会できません。',
    'withdrawal_payment_error' => '口座登録が未登録のため売上金の振込できません。口座登録してから退会してください。',

    'product_mein_file' => 'メイン画像は必須です。',
    'product_already_bought' => 'この商品は既に購入されています。',

    'event_file' => 'イベント画像は必須です。',

    'product_point' => '保有しているポインまでしか使用できません。',

    'system_password_reset' => '該当するアドレスがありません',

    'referral_code_err' => '紹介コードが存在しません',

    'shipping_method' => '現在は匿名発送は使用できません。しばらくお待ちください',

    'shipping_area' => '発送元の地域は必須です。',

    'faild_yamato_regist' => 'ヤマト匿名発送の登録でエラーとなりました。',

    'bank_name' => 'セイとメイの間は空白を入れてください。',

    'faild_create_event_oubo' => "申し訳ございません。\nご応募に関する処理でエラーが発生しました。\nお手数ですが、再度ご応募をお願いいたします。\n再度ご応募いただいてもエラーが発生する場合は運営までお問合せください。",
    'failed_charge_event_oubo' => "申し訳ございません。\n決済に関する処理でエラーが発生したため、イベントの応募ができませんでした。\nお手数ですが、カード情報についてご確認お願いいたします。",


];
