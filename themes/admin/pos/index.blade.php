@extends('admin.layouts.admin')

@section('page-title','Sale')
@section('heading','Sale')
@section('breadcrumbs', 'Sale')

@section('content')

    <form method="post" action="#" name="form-cart" id="form-cart" class="form-horizontal">
        @csrf
        <div id="tables">
            <div class="form-group row">
                <input type="hidden" name="customer_id" value="Walking Customer" id="customer" readonly
                       class="form-control"/>

                <div class="col-md-6">
                    @include('admin.pos.products')
                </div>
                <div class="col-md-6">

                    <label><strong>Product Name:</strong></label>
                    <select name="products" id="products" data-placeholder="Select Product"
                            class="form-control select2"></select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-7">

                    <div class="card border-dark">
                        <div class="card-header bg-dark text-white header-elements-inline">
                            <h6 class="card-title">Cart</h6>
                            <button type="button" data-action="emptyCart"
                                    class="btn btn-success">Empty Cart
                            </button>
                        </div>
                        <div class="card-body" id="cart">
                        </div>
                    </div>

                    {{--				@include('admin.order.products')--}}
                </div>
                <div class="col-md-5">
                    @include('admin.order.payment')
                </div>
            </div>
        </div>
        <input type="hidden" id="cart_url" value="{{route('order.show_cart',1)}}">
    </form>
@endsection
@include('plugins.ajax')
@include('plugins.DataTables')
@include('plugins.select2')

@push('footer')

    <script>


        $(document).ready(function () {
            refreshCart()
            $('body').addClass('sidebar-xs')
            let newPayment = new Option('Cash', 0, false, false)
            $('#payments').select2({
                ajax: {
                    url: "{{route('select2.banks')}}",
                    type: 'post',
                    data: function (params) {
                        return {
                            term: params.term
                        }
                    }
                },
            }).append(newPayment).trigger('change')

            $('#products').select2({
                ajax: {
                    url: "{{route('select2.products')}}",
                    type: 'post',
                    data: function (params) {
                        return {
                            term: params.term
                        }
                    }
                },
            }).on('change', function () {
                let id = $(this).val()
                $.ajax({
                    url: '{{route('products.get')}}',
                    type: 'post',
                    dataType: 'json',
                    data: {id: id},
                    success: function (res) {
                        if (res.status === 'ok') {
                            ui.successMessage(res.message)
                            refreshCart()
                            return true
                        }
                        ui.errorMessage(res.message)
                    },
                    error: function (res) {
                        ui.ajaxError(res)
                    }
                })
            })

        })

        ui.$body.on('click', '[data-action="addCart"]', function (e) {
            e.preventDefault()
            let $el = $(this)
            let id = $el.data('id')
            let product = $el.data('name')
            let price = $el.data('price')
            let taxable_price = $el.data('actual')

            let purchase_price = $el.data('purchase_price')
            let stock = $('.stock' + id).val()
            let url = '{{route("order.add_to_cart", 1) }}'
            $.ajax({
                url: url,
                type: 'post',
                dataType: 'json',
                data: {
                    'id': id,
                    'sale_price': price,
                    'taxable_price': taxable_price,
                    'purchase_price': purchase_price,
                    'stock': stock
                },
                success: function (res) {
                    if (res.status === 'ok') {
                        ui.successMessage(res.message)
                        refreshCart()
                        return true
                    }
                    ui.errorMessage(res.message)
                },
                error: function (res) {
                    ui.ajaxError(res)

                }
            })
        })

        function updateCart(id) {
            let url = '{{route("order.add_to_cart", 1) }}'
            let stock = $('.stockCart' + id).val()
            let taxable_price = $('.priceCart' + id).val()
            let price = $('.actualCart' + id).val()
            let purchase_price = $('.pPriceCart' + id).val()
            console.log(taxable_price)
            $.ajax({
                url: url,
                type: 'post',
                dataType: 'json',
                data: {
                    'id': id,
                    'price': price,
                    'taxable_price': taxable_price,
                    'purchase_price': purchase_price,
                    'stock': stock
                },
                success: function (res) {
                    if (res.status === 'ok') {
                        refreshCart()
                        return true
                    }
                    ui.errorMessage(res.message)
                },
                error: function (res) {
                    ui.ajaxError(res)
                }
            })

        }

        ui.$body.on('click', '[data-action="emptyCart"]', function (e) {
            e.preventDefault()
            $.confirm({
                title: 'Empty Cart?',
                content: 'Do you want to Empty this Cart?',
                type: 'red',
                buttons: {
                    confirm: function () {
                        $.ajax({
                            url: '{{route('order.empty_cart', 1)}}',
                            type: 'post',
                            dataType: 'json',
                            success: function (res) {
                                if (res.status === 'ok') {
                                    ui.successMessage(res.message)
                                    refreshCart()
                                    return true
                                }
                                ui.errorMessage(res.message)
                            },
                            error: function (res) {
                                ui.ajaxError(res)
                            }
                        })
                    }
                }
            })
        })

        ui.$body.on('click', '[data-action="plus"]', function (e) {
            e.preventDefault()
            let $el = $(this)
            let id = $el.data('id')
            let table = $('#table-order-product')
            let td = table.find('tbody > tr').children('td')
            let input = td.find('.stock' + id)
            input.val(parseInt(input.val()) + 1)

        })

        ui.$body.on('click', '[data-action="minus"]', function (e) {
            e.preventDefault()
            let $el = $(this)
            let id = $el.data('id')
            let table = $('#table-order-product')
            let td = table.find('tbody > tr').children('td')
            let input = td.find('.stock' + id)
            input.val(parseInt(input.val()) - 1)

        })

        function totalAmount() {
            let netAmount = 0
            let sumPrice = 0
            $('#table-cart > tbody > tr').each(function () {
                let self = $(this).find(':input')
                netAmount += parseFloat(self.eq(1).val()) * parseFloat(self.eq(2).val())
                sumPrice += parseFloat(self.eq(0).val()) * parseFloat(self.eq(1).val())
            })

            $('#price').val(parseFloat(sumPrice))
            $('#netAmount').val(parseFloat(netAmount))
            proceed()

        }

        function round(value, precision) {
            let aPrecision = Math.pow(10, precision)
            return Math.round(value * aPrecision) / aPrecision
        }

        function proceed() {

            let discount = $('#disc').val()
            let dis = $('#discount').val()
            //let vat = '{{option('vat_amount')}}'
            let price = $('#netAmount').val()
            let delivery_charges = $('#delivery').val()
            // let amountVat = (parseFloat(price) * parseFloat(vat)) / 100
            let total
            if (discount === 'per') {
                let amountDisc = (parseFloat(price) * parseFloat(dis)) / 100
                total = (parseFloat(price) + parseFloat(delivery_charges) - parseFloat(amountDisc))
            } else {
                total = parseFloat(price) + parseFloat(delivery_charges) - parseFloat(dis)
            }
            //total += parseFloat(amountVat)
            $('#netAmount').val(round(parseFloat(total), 2))
            $('#paid').val(round(parseFloat(total), 2))
            changed()
        }

        function changed() {
            let paid = $('#paid').val()
            let netAmount = $('#netAmount').val()
            let chanage = paid - netAmount
            $('#change').val(round(chanage, 2))
        }

        function deleteRow(id) {
            $.confirm({
                title: 'Delete?',
                content: 'Do you want to delete this product?',
                type: 'red',
                buttons: {
                    confirm: function () {
                        $.ajax({
                            url: '{{route('order.delete_from_cart', 1)}}',
                            type: 'post',
                            dataType: 'json',
                            data: {
                                'id': id
                            },
                            success: function (res) {
                                if (res.status === 'ok') {
                                    ui.successMessage(res.message)
                                    refreshCart()
                                    return true
                                }
                                ui.errorMessage(res.message)
                            },
                            error: function (res) {
                                ui.ajaxError(res)
                            }
                        })
                    },
                    cancel: function () {
                    }
                }
            })
        }

        ui.$body.on('click', '[data-action="save"]', function (e) {
            e.preventDefault()
            let products = []
            $('#table-cart tr').each(function (row, tr) {
                    if ($(tr).find('td:eq(0)').text() == '') {
                    } else {
                        let sub = {
                            'id': $(tr).find('td:eq(0)').text(),
                            'sale_price': $(tr).find(':input').eq(0).val(),
                            'stock': $(tr).find('td:eq(2)').find('input').val(),
                            'taxable_price': $(tr).find('td:eq(3)').find('input').val(),
                            'purchase_price': $(tr).find('input[type=hidden]').val(),
                        }
                        products.push(sub)
                    }
                }
            )

            $.confirm({
                title: 'Are you sure?',
                content: 'You won\'t be able to revert this!',
                type: 'warning',
                buttons: {
                    confirm: function () {
                        let price = $('#price').val()
                        let delivery_charges = $('#delivery').val()
                        let discount = $('#discount').val()
                        let netAmount = $('#netAmount').val()
                        let date = $('#date').val()
                        let amount = $('#paid').val()
                        let payment_id = $('#payments').val()
                        let payment_type = 'Cash'
                        if (products.length > 0) {
                            $.ajax({
                                url: '{{route('order.add',1)}}',
                                type: 'post',
                                dataType: 'json',
                                data: {
                                    products: products,
                                    sub_total: price,
                                    total: netAmount,
                                    discount: discount,
                                    date: date,
                                    payment_id: payment_id,
                                    payment: price,
                                    delivery_charges: delivery_charges,
                                    amount: amount,
                                    payment_type: payment_type,
                                    customer_name: $('#customer_name').val(),
                                    cashier_name: $('#cashier_name').val(),
                                    printer: $('#printer').val()
                                },
                                success: function (res) {
                                    if (res.status === 'ok') {
                                        ui.successMessage(res.message)
                                        refreshCart()
                                        window.location = res.url
                                        return true
                                    }
                                    ui.errorMessage(res.message)
                                },
                                error: function (res) {
                                    ui.ajaxError(res)
                                }
                            })

                        } else {
                            ui.errorMessage('Enter Stock to Proceed')
                        }

                    },
                    cancel: function () {

                    }
                }
            })

        })

        function refreshCart() {

            $.ajax({
                url: $('#cart_url').val(),
                type: 'patch',
                success: function (res) {
                    $('#cart').html(res)
                    totalAmount()
                }
            })
        }

        key('s', function () {
                refreshCart()
                let products = []
                $('#table-cart tr').each(function (row, tr) {
                        if ($(tr).find('td:eq(0)').text() == '') {
                        } else {
                            let sub = {
                                'id': $(tr).find('td:eq(0)').text(),
                                'sale_price': $(tr).find(':input').eq(0).val(),
                                'stock': $(tr).find('td:eq(2)').find('input').val(),
                                'taxable_price': $(tr).find('td:eq(3)').find('input').val(),
                                'purchase_price': $(tr).find('input[type=hidden]').val(),
                            }
                            products.push(sub)
                        }
                    }
                )

                let price = $('#price').val()
                let delivery_charges = $('#delivery').val()
                let discount = $('#discount').val()
                let netAmount = $('#netAmount').val()
                let date = $('#date').val()
                let amount = $('#paid').val()
                let payment_id = $('#payments').val()
                let payment_type = 'Cash'
                if (products.length > 0) {
                    $.ajax({
                        url: '{{route('order.add',1)}}',
                        type: 'post',
                        dataType: 'json',
                        data: {
                            products: products,
                            sub_total: price,
                            total: netAmount,
                            discount: discount,
                            date: date,
                            payment_id: payment_id,
                            payment: price,
                            delivery_charges: delivery_charges,
                            amount: amount,
                            payment_type: payment_type,
                            customer_name: $('#customer_name').val(),
                            cashier_name: $('#cashier_name').val(),
                            printer: $('#printer').val()
                        },
                        success: function (res) {
                            if (res.status === 'ok') {
                                ui.successMessage(res.message)
                                refreshCart()
                                document.location.href = res.url
                                return

                            }
                            ui.errorMessage(res.message)
                        },
                        error: function (res) {
                            ui.ajaxError(res)
                        }
                    })
                } else {
                    ui.errorMessage('Enter Stock to Proceed')
                }
            }
        )

    </script>
@endpush()
