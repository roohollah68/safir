شماره درخواست: {{$withdrawal->id}}
@if($withdrawal->user_file)
    فایل درخواست: {{env('APP_URL')}}withdrawal/{{$withdrawal->user_file}}
@endif
کاربر ثبت کننده: {{$withdrawal->user->name}}
مبلغ درخواستی: {{number_format($withdrawal->amount)}}
بابت: {{$withdrawal->expense}}
مکان درخواست: {{config('withdrawalLocation')[$withdrawal->location]}}
توضیحات کاربر: {{$withdrawal->user_desc}}
روش پرداخت: {{$withdrawal->pay_method=='cash'?'نقدی':'چکی'}}
صاحب حساب یا دریافت کنند چک: {{$withdrawal->account_name}}
@if($withdrawal->pay_method=='cash')
    شماره شبا یا کارت: {{$withdrawal->account_number}}
@else
    کد ملی یا شناسه ملی: {{$withdrawal->cheque_id}}
    تاریخ چک: {{verta($withdrawal->cheque_date)->formatJalaliDate()}}
@endif
*حسابداری*===========================
تایید حسابداری: {{ strip_tags($withdrawal->counter_status()) }}
توضیحات حسابداری: {{$withdrawal->counter_desc}}
دسته هزینه: {{$withdrawal->expense_type=='current'?'هزینه':'دارایی'}}
نوع هزینه: {{$withdrawal->expense_desc}}
نوع فاکتور: {{$withdrawal->official != 1?'غیر رسمی':('رسمی '.($withdrawal->vat == 1?'با ارزش افزوده':'بدون ارزش افزوده'))}}
بانک پرداخت کننده: {{(isset($withdrawal->bank))?$withdrawal->bank->name:'نامشخص'}}
@if($withdrawal->counter_confirm != 0)
    *مدیر*===========================
    تایید مدیر: {{ strip_tags($withdrawal->manager_status()) }}
    توضیحات مدیر: {{$withdrawal->manager_desc}}
@endif
@if($withdrawal->counter_confirm != 0 && $withdrawal->manager_confirm != 0)
    *پرداخت*===========================
    تایید پرداخت: {{ strip_tags($withdrawal->payment_status()) }}
    توضیحات پرداخت: {{$withdrawal->payment_desc}}
    @if($withdrawal->payment_file)
        رسید پرداخت: {{env('APP_URL')}}withdrawal/{{$withdrawal->payment_file}}
    @endif
    @if($withdrawal->payment_file2)
        رسید پرداخت2: {{env('APP_URL')}}withdrawal/{{$withdrawal->payment_file2}}
    @endif
    @if($withdrawal->payment_file3)
        رسید پرداخت3: {{env('APP_URL')}}withdrawal/{{$withdrawal->payment_file3}}
    @endif
@endif
@if($withdrawal->counter_confirm != 0 && $withdrawal->manager_confirm != 0 && $withdrawal->payment_confirm != 0)
    *دریافت*===========================
    تایید دریافت کالا یا خدمات: {{ strip_tags($withdrawal->recipient_status()) }}
    توضیحات دریافت: {{$withdrawal->recipient_desc}}
    @if($withdrawal->recipient_file)
        رسید دریافت: {{env('APP_URL')}}withdrawal/{{$withdrawal->recipient_file}}
    @endif
@endif
