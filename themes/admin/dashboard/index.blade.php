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
            <div class="row match-height">
                <div class="col-xl-6 col-12">
                    <div class="card">
                        <div
                            class="
            card-header
            d-flex
            flex-sm-row flex-column
            justify-content-md-between
            align-items-start
            justify-content-start
          "
                        >
                        </div>
                        <div class="card-body">
                            <div id="banks"></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-12">
                    <div class="card">
                        <div
                            class="
            card-header
            d-flex
            flex-sm-row flex-column
            justify-content-md-between
            align-items-start
            justify-content-start
          "
                        >
                        </div>
                        <div class="card-body">
                            <div id="counting"></div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="row match-height">
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
            <div class="row match-height">
                <div class="col-xl-12 col-12">
                    <div class="card">
                        <div
                            class="
            card-header
            d-flex
            flex-sm-row flex-column
            justify-content-md-between
            align-items-start
            justify-content-start
          "
                        >
                        </div>
                        <div class="card-body">
                            <div id="monthlyProfit"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection


@push('footer')
    <script>
        $(document).ready(function () {
            $('body').addClass('sidebar-xs')
        })
        var $counting = {
            series: [{{$users}}, {{$vendors}}, {{$customers}}, {{$categories}}, {{$products}}],
            chart: {
                width: 380,
                type: 'pie',
            },
            labels: ['Users', 'Vendors', 'Customers', 'Categories', 'Products'],
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val
                    }
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val, opt) {
                    return opt.w.config.series[opt.seriesIndex]
                }
            },
            title: {
                text: 'Summery'
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        }
        new ApexCharts(document.querySelector('#counting'), $counting).render()

        var transactionsOptions = {
            series: [
                {
                    data: [
                        @foreach($bank_details as $bank)
                            {{round($bank['amount'], 2)}},
                        @endforeach
                    ]
                }
            ],
            chart: {
                type: 'bar',
                height: 380
            },

            plotOptions: {
                bar: {
                    barHeight: '100%',
                    distributed: true,
                    horizontal: true,
                    dataLabels: {
                        position: 'bottom'
                    },
                }
            },
            colors: ['#33b2df', '#546E7A', '#d4526e', '#13d8aa', '#A5978B', '#2b908f', '#f9a3a4', '#90ee7e',
                '#f48024', '#69d2e7'
            ],
            dataLabels: {
                enabled: true,
                textAnchor: 'start',
                formatter: function (val) {
                    return 'Rs ' + val
                },
                offsetX: 0,
            },
            yaxis: {
                title: {
                    text: 'Banks Payment History'
                }
            },
            stroke: {
                width: 1,
                colors: ['#fff']
            },
            xaxis: {
                categories: [

                    @foreach($bank_details as $bank)
                        "{!! $bank['name'] !!}",
                    @endforeach
                ]
            },
            legend: {
                position: 'top'
            }
        }

        new ApexCharts(document.querySelector('#banks'), transactionsOptions).render()

        var monthlyProfit = {
            series: [
                {
                    name: 'Stock',
                    type: 'column',
                    data: [
                        @foreach($month as $m)
                            {{$m['stock']}},
                        @endforeach
                    ]
                },
                {
                    name: 'Profit',
                    type: 'column',
                    data: [@foreach($month as $m)
                        {{$m['profit']}},
                        @endforeach]
                },
                {
                    name: 'Discount',
                    type: 'line',
                    data: [@foreach($month as $m)
                        {{$m['discount']}},
                        @endforeach]
                },
                {
                    name: 'Vendor Discount',
                    type: 'line',
                    data: [@foreach($month as $m)
                        {{$m['vendor_discount']}},
                        @endforeach]
                },
                {
                    name: 'Net Profit',
                    type: 'line',
                    data: [@foreach($month as $m)
                        {{$m['net'] - $m['discount'] + $m['vendor_discount']}},
                        @endforeach]
                },
            ],
            chart: {
                height: 350,
                type: 'line',
                stacked: false
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                width: [1, 1, 1, 1, 4]
            },
            title: {
                text: 'Month Wise Profit',
                align: 'left',
                offsetX: 110
            },
            xaxis: {
                categories: [
                    @foreach($month as $m)
                        '{{$m['month']}}',
                    @endforeach
                ]
            },

            tooltip: {
                fixed: {
                    enabled: true,
                    position: 'topLeft', // topRight, topLeft, bottomRight, bottomLeft
                    offsetY: 30,
                    offsetX: 60
                },
            },
            legend: {
                horizontalAlign: 'left',
                offsetX: 40
            }
        }

        new ApexCharts(document.querySelector('#monthlyProfit'), monthlyProfit).render()


    </script>
@endpush

