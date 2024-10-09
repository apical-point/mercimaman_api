
{!! config('const.site.SITE_NAME') !!}事務局様

以下の商品がキャンセルされました。返金手続きをお願いします。

商品番号：{{$data['product_id']}}
商品名：{{$data['product_name']}}
注文番号：{{$data['order_id']}}

@include('emails.common.admin_footer')
