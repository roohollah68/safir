@extends('layout.main')

@section('title')
    آمار فروش محصولات
@endsection

@section('content')
    <form action="" method="post">
        @csrf
        <div class="input-group col-12 mb-3">
            <div class="col-md-6 d-flex">
                <span class="input-group-text cursor-pointer" id="date1">📅</span>
                <input type="text" name="from" class="form-control" placeholder="از تاریخ" id="date1-text" required>
            </div>
            <div class=" col-md-6 d-flex">
                <span class="input-group-text cursor-pointer" id="date2">📅</span>
                <input type="text" name="to" class="form-control" placeholder="تا تاریخ" id="date2-text" required>
            </div>
        </div>
        @if($User->meta('statistic'))
            <span>نوع فروشنده:</span>
            <label for="safirOrders">سفیران</label>
            <input type="checkbox" id="safirOrders" name="safirOrders"
                   class="checkboxradio" @checked(isset($request->safirOrders))>
            <label for="siteOrders">سایت ها</label>
            <input type="checkbox" id="siteOrders" name="siteOrders"
                   class="checkboxradio" @checked(isset($request->siteOrders))>
            <label for="adminOrders">فروشگاه ها</label>
            <input type="checkbox" id="adminOrders" name="adminOrders"
                   class="checkboxradio" @checked(isset($request->adminOrders))>
            <br>
        @else
            <input type="hidden" name="safirOrders" value="true">
            <input type="hidden" name="siteOrders" value="true">
            <input type="hidden" name="adminOrders" value="true">
        @endif
        <div class="row">
            @if($User->meta('statistic'))
                <div class="col-md-4 my-3">
                    <div class="form-group d-flex">
                        <label for="user" class="input-group-text">فروشنده:</label>
                        <select class="form-control" name="user" id="user" onchange="customerList()">
                            <option value="" selected>همه</option>
                            @foreach($users as $id=>$user)
                                @continue($user->deleted_at)
                                <option value="{{$id}}" @selected($request->user == $id)>{{$user->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @else
                <input type="hidden" id="user" name="user" value="{{$User->id}}">
            @endif
            <div class="col-md-4 my-3">
                <div class="form-group d-flex">
                    <label for="customer" class="input-group-text">مشتری:</label>
                    <input type="text" name="customer" value="{{$request->customer}}" id="customer"
                           class="form-control" @readonly(!$request->user)>
                </div>
            </div>
        </div>

        <label for="productBase">بر اساس محصول</label>
        <input type="radio" name="base" value="productBase" id="productBase"
               class="checkboxradio" @checked($request->base=='productBase')>
        @if($User->meta('statistic'))
            <label for="safirBase">بر اساس فروشنده</label>
            <input type="radio" name="base" value="safirBase" id="safirBase"
                   class="checkboxradio" @checked($request->base=='safirBase')>
        @endif
        <label for="customerBase">بر اساس مشتری</label>
        <input type="radio" name="base" value="customerBase" id="customerBase"
               class="checkboxradio" @checked($request->base=='customerBase')>
        <label for="paymentBase">بر اساس نحوه پرداخت</label>
        <input type="radio" name="base" value="paymentBase" id="paymentBase"
               class="checkboxradio" @checked($request->base=='paymentBase')>
        <label for="depositBase">بر اساس ثبت واریزی</label>
        <input type="radio" name="base" value="depositBase" id="depositBase"
               class="checkboxradio" @checked($request->base=='depositBase')>
        <label for="cityBase">بر اساس شهر</label>
        <input type="radio" name="base" value="cityBase" id="cityBase"
               class="checkboxradio" @checked($request->base=='cityBase')>
        <br>
        <input class="btn btn-success m-3" type="submit" value="اعمال فیلتر">

        @if(isset($totalSale))
            @if($request->base=='productBase')
                <br>
                <h4>مجموع فروش در این دوره : <span>{{number_format($totalSale)}}</span> ریال </h4>
                @if($User->meta('statistic'))
                    <h4>مجموع سود در این دوره : <span>{{number_format($totalProfit)}}</span> ریال </h4>
                @endif
                <h4>تعداد سفارشات در این دوره : <span>{{$orderNumber}}</span> عدد </h4>
                <h4>تعداد محصولات فروخته شده : <span>{{$productNumber}}</span> عدد </h4>
                <br>
                <table class="table table-striped" id="statistic-table">
                    <thead>
                    <tr>
                        <th>نام محصول</th>
                        <th>تعداد فروش</th>
                        <th>مبلغ کل(ریال)</th>
                        <th>قیمت میانگین(ریال)</th>
                        @if($User->meta('statistic'))
                            <th>قیمت تولید(ریال)</th>
                            <th>سود(ریال)</th>
                        @endif
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($goods as $good)
                        <tr>
                            {{--                        <td><a href="/product/edit/{{$product->id}}">{{$product->name}}</a></td>--}}
                            <td><a class="text-decoration-none" href="{{ route('productChart', ['id' => $good->id]) }}">{{$good->name}}</a></td>
                            <td>{{$good->number}}</td>
                            <td>{{number_format($good->total)}}</td>
                            <td>{{number_format(($good->number>0)?$good->total/$good->number:0)}}</td>
                            @if($User->meta('statistic'))
                                <td>{{number_format($good->productPrice)}}</td>
                                <td>{{number_format($good->profit)}}</td>
                            @endif

                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
            @if($request->base=='safirBase')
                <br>
                <h4>مجموع فروش در این دوره : <span>{{number_format($totalSale)}}</span> ریال </h4>
                <h4>تعداد سفارشات در این دوره : <span>{{$orderNumber}}</span> عدد </h4>
                <br>
                <table class="table table-striped" id="statistic-table">
                    <thead>
                    <tr>
                        <th>نام فرروشنده</th>
                        <th>تعداد فروش</th>
                        <th>مبلغ کل(ریال)</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{$user->name}}</td>
                            <td>{{$user->orderNumber}}</td>
                            <td>{{number_format($user->totalSale)}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
            @if($request->base=='customerBase')
                <br>
                <h4>مجموع فروش در این دوره : <span>{{number_format($totalSale)}}</span> ریال </h4>
                <h4>تعداد سفارشات در این دوره : <span>{{$orderNumber}}</span> عدد </h4>
                <br>

                <table class="table table-striped" id="statistic-table">
                    <thead>
                    <tr>
                        <th>نام مشتری</th>
                        <th>تعداد فروش</th>
                        <th>مبلغ کل(ریال)</th>
                        <th>کاربر مرتبط</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($customers as $customer)
                        <tr>
                            <td><a href="/customer/transaction/{{$customer->id}}">{{$customer->name}}</a></td>
                            <td>{{$customer->orderNumber}}</td>
                            <td>{{number_format($customer->totalSale)}}</td>
                            <td>{{$users[$customer->user_id]->name}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
            @if($request->base=='paymentBase')
                <br>
                <h4>مجموع فروش در این دوره : <span>{{number_format($totalSale)}}</span> ریال </h4>
                <h4>تعداد سفارشات در این دوره : <span>{{$orderNumber}}</span> عدد </h4>
                <br>
                <table class="table table-striped" id="statistic-table">
                    <thead>
                    <tr>
                        <th>شیوه پرداخت</th>
                        <th>تعداد فروش</th>
                        <th>مبلغ کل(ریال)</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($paymentMethods as $index => $paymentMethod)
                        <tr>
                            <td>{{$index}}</td>
                            <td>{{$paymentMethod->orderNumber}}</td>
                            <td>{{number_format($paymentMethod->totalSale)}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
            @if($request->base=='depositBase')
                <br>
                <h4>مجموع فروش در این دوره : <span>{{number_format($totalSale)}}</span> ریال </h4>
                <h4>تعداد واریزی ها در این دوره : <span>{{$orderNumber}}</span> عدد </h4>
                <br>
                <table class="table table-striped" id="statistic-table">
                    <thead>
                    <tr>
                        <th>مشتری</th>
                        <th>کاربر مرتبط</th>
                        <th>مبلغ کل(ریال)</th>
                        <th>تعداد واریز</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($customers as $id => $customer)
                        <tr>
                            <td><a href="/customer/transaction/{{$customer->id}}">{{$customer->name}}</a></td>
                            <td>{{$users[$customer->user_id]->name}}</td>
                            <td>{{number_format($customer->total)}}</td>
                            <td>{{$customer->number}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
            @if($request->base=='cityBase')
                <br>
                <h4>مجموع فروش در این دوره : <span>{{number_format($totalSale)}}</span> ریال </h4>
                <h4>تعداد سفارشات در این دوره : <span>{{$orderNumber}}</span> عدد </h4>
                <br>

                <table class="table table-striped" id="statistic-table">
                    <thead>
                    <tr>
                        <th>نام شهر</th>
                        <th>تعداد فروش</th>
                        <th>مبلغ کل(ریال)</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($cities as $id => $city)
                        <tr>
                            <td>
                                <button class="btn btn-outline-primary" name="city" type="submit"
                                        value="{{$id}}">{{$city->name}}</button>
                            </td>
                            <td>{{$city->orderNumber}}</td>
                            <td>{{number_format($city->totalSale)}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        @endif
    </form>
@endsection
@section('files')
    <script>
        let users = {!!$users!!};
        $(function () {
            $('#statistic-table').DataTable({
                order: [[2, "desc"]],
                language: language,
                layout: {
                    topStart: {
                        buttons: ['copyHtml5', 'excelHtml5', 'csvHtml5', 'pdfHtml5']
                    }
                }
            });
            $(".checkboxradio").checkboxradio();

            const date1 = new mds.MdsPersianDateTimePicker($('#date1')[0], {
                targetTextSelector: '#date1-text',
                selectedDate: new Date('{{$request->from}}'),
                selectedDateToShow: new Date('{{$request->from}}'),
            });
            const date2 = new mds.MdsPersianDateTimePicker($('#date2')[0], {
                targetTextSelector: '#date2-text',
                selectedDate: new Date('{{$request->to}}'),
                selectedDateToShow: new Date('{{$request->to}}'),
            });
            customerList();
        });

        function customerList() {
            let customerId = $('#user').val()
            if (!customerId) {
                $("#customer").val('همه').prop('readonly', true);
            } else {
                $("#customer").prop('readonly', false).autocomplete({
                    source: Object.keys(users[customerId].customer),
                });
            }

        }
    </script>

@endsection
