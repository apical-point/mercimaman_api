{!! config('const.site.SITE_NAME') !!} 運営事務局様

バッチ処理でエラーが発生しました。
ログを確認してください。

@if (isset($data['name']))
処理名：{{$data['name']}}
@endif
@if (isset($data['user']))
ユーザーID：{{$data['user']}}
@endif
@if (isset($data['userShop']))
ユーザー店舗：{{$data['userShop']}}
@endif
@if (isset($data['order']))
注文ID：{{$data['order']['id']}}
ユーザーID：{{$data['order']['user_id']}}
@endif

@include('emails.common.footer')
