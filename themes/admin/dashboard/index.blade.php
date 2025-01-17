@extends('admin.layouts.admin')
@section('page-title','Dashboard')
@section('heading','Dashboard')
@section('breadcrumbs', 'Dashboard')

@section('heading-buttons')

@endsection

@section('breadcrumbs')
    <span class="breadcrumb-item active">Dashboard</span>
@endsection

@section('breadcrumb-buttons')

@endsection

@section('content')

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="table-responsive col-md-3">
                    <div
                        class="card-header bg-teal-400 header-elements-inline">
                        <h6 class="card-title">Summary</h6>
                    </div>

                    <table class="table table-condensed table-bordered">
                        <thead>
                        <tr>
                            <th>Type</th>
                            <th>Count</th>
                        </tr>
                        </thead>
                        <tbody>

                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <a href="#" class="btn btn-primary rounded-pill btn-icon btn-sm">
                                            <span class="letter-icon">U</span>
                                        </a>
                                    </div>
                                    <div>
                                        <a href="#" class="text-body font-weight-semibold letter-icon-title">Users</a>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted font-size-sm">{{$users}}</span>
                            </td>

                        </tr>

                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <a href="#" class="btn btn-success rounded-pill btn-icon btn-sm">
                                            <span class="letter-icon">C</span>
                                        </a>
                                    </div>
                                    <div>
                                        <a href="#"
                                           class="text-body font-weight-semibold letter-icon-title">Customers</a>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted font-size-sm">{{$customers}}</span>
                            </td>

                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <a href="#" class="btn btn-secondary rounded-pill btn-icon btn-sm">
                                            <span class="letter-icon">V</span>
                                        </a>
                                    </div>
                                    <div>
                                        <a href="#" class="text-body font-weight-semibold letter-icon-title">Vendors</a>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted font-size-sm">{{$vendors}}</span>
                            </td>

                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <a href="#" class="btn btn-success rounded-pill btn-icon btn-sm">
                                            <span class="letter-icon">C</span>
                                        </a>
                                    </div>
                                    <div>
                                        <a href="#"
                                           class="text-body font-weight-semibold letter-icon-title">Categories</a>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted font-size-sm">{{$categories}}</span>
                            </td>

                        </tr>

                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <a href="#" class="btn btn-danger rounded-pill btn-icon btn-sm">
                                            <span class="letter-icon">P</span>
                                        </a>
                                    </div>
                                    <div>
                                        <a href="#"
                                           class="text-body font-weight-semibold letter-icon-title">Products</a>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted font-size-sm">{{$products}}</span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="table-responsive col-md-3">
                    <div
                        class="card-header bg-teal-400 header-elements-inline">
                        <h6 class="card-title">Bank Payments</h6>
                    </div>

                    <table class="table table-condensed table-bordered">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Amount</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($bank_details as $bank)

                            <tr>
                                <th>{{$bank['name']}}</th>
                                <td>{{$bank['amount']}}</td>
                            </tr>

                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="table-responsive col-md-3">
                    <div
                        class="card-header bg-teal-400 header-elements-inline">
                        <h6 class="card-title">Expenses</h6>
                    </div>

                    <table class="table table-condensed table-bordered">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Amount</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($expense_details as $ex)

                            <tr>
                                <th>{{$ex['name']}}</th>
                                <td>{{$ex['amount']}}</td>
                            </tr>

                        @endforeach
                        </tbody>
                    </table>


                </div>
            </div>

            @if($month)
                <div class="row">
                    <div class="table-responsive">
                        <div
                            class="card-header bg-teal-400 header-elements-inline">
                            <h6 class="card-title">Monthly Profit</h6>
                        </div>

                        <table class="table table-condensed table-bordered">
                            <thead>
                            <tr>
                                <th>Month</th>
                                <th>Sub Total</th>
                                <th>Daily Discount</th>
                                <th>Daily Delivery</th>
                                <th>Customer Discount</th>
                                <th>Vendor Discount</th>
                                <th>Expense</th>
                                <th>Net Amount</th>
                            </tr>
                            </thead>


                            <tbody>
                            @foreach($month as $m)
                                <tr>
                                    <th>{{$m['month']}}</th>
                                    <td>{{$m['sub_total']}}</td>
                                    <td>{{$m['daily_discount']}}</td>
                                    <td>{{$m['daily_delivery']}}</td>
                                    <td>{{$m['discount']}}</td>
                                    <td>{{$m['vendor_discount']}}</td>
                                    <td>{{$m['expense']}}</td>
                                    <td>{{$m['net']}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <td><strong>Total</strong></td>
                                <td><strong>{{ collect($month)->sum('sub_total') }}</strong></td>
                                <td><strong>{{ collect($month)->sum('daily_discount') }}</strong></td>
                                <td><strong>{{ collect($month)->sum('daily_delivery') }}</strong></td>
                                <td><strong>{{ collect($month)->sum('discount') }}</strong></td>
                                <td><strong>{{ collect($month)->sum('vendor_discount') }}</strong></td>
                                <td><strong>{{ collect($month)->sum('expense') }}</strong></td>
                                <td><strong>{{ collect($month)->sum('net') }}</strong></td>
                            </tr>
                            </tfoot>
                        </table>

                    </div>
                </div>
            @endif

            @if($data)
                <div class="row">
                    <div class="table-responsive">
                        <div
                            class="card-header bg-teal-400 header-elements-inline">
                            <h6 class="card-title">Daily Profit</h6>
                        </div>

                        <table class="table table-condensed table-bordered">
                            <thead>
                            <tr>
                                <th>Date</th>
                                <th>Total Sale</th>
                                <th>Delivery Charges</th>
                                <th>Discount</th>
                                <th>Net Amount</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($data as $d)
                                <tr>
                                    <th>{{date('d-m-Y', strtotime($d['date']))}}</th>
                                    <td>{{$d['total']}}</td>
                                    <td>{{$d['delivery']}}</td>
                                    <td>{{$d['discount']}}</td>
                                    <td>{{$d['net']}}</td>
                                </tr>

                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <td><strong>Total</strong></td>
                                <td><strong>{{ collect($data)->sum('total') }}</strong></td>
                                <td><strong>{{ collect($data)->sum('delivery') }}</strong></td>
                                <td><strong>{{ collect($data)->sum('discount') }}</strong></td>
                                <td><strong>{{ collect($data)->sum('net') }}</strong></td>
                            </tr>
                            </tfoot>
                        </table>

                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection


@push('head')
    {{--Any Style or head tag data--}}
@endpush

