パスワードの再設定を受け付けました。

下記のURLより、「{!! config('const.site.SITE_NAME') !!}」のパスワードを再設定してください。

{!! config('const.site.DEMAE_BASE_URL') !!}auth/newPassword/?param={{$data['param']}}
有効期限 : {{$data['regist_limit']}}


本メールにお心当たりがない場合は、大変お手数ですが、以下までご連絡をいただきますよう
お願いいたします。

{{$data['sig']}}
