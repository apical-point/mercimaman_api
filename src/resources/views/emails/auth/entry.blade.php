{{$data['email']}} 様

初めまして！ 「{!! config('const.site.SITE_NAME') !!}」です。
この度は、登録へお手続きをいただきましてありがとうございます。

仮登録を受け付けました。

以下のURLをクリックして本登録を完了していただきますようお願いいたします。

{!! config('const.site.DEMAE_BASE_URL') !!}auth/regist/?param={{$data['param']}}
有効期限 : {{$data['regist_limit']}}

本メールにお心当たりがない場合は、大変お手数ですが、以下までご連絡をいただきますよう
お願いいたします。

{{$data['sig']}}
