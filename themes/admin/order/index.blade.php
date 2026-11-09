@extends('admin.layouts.admin')

@section('page-title','Sale')
@section('heading','Sale')
@section('breadcrumbs', 'Sale')

@section('content')
    <form method="post" action="#" name="form-cart" id="form-cart" class="form-horizontal">
        @csrf
        <input type="hidden" name="customer_id" id="customer_id" value="{{$customer->id}}">
        <input type="hidden" id="cart_url" value="{{route('order.show_cart',$customer->id)}}">
        <div class="form-group row">
            <div class="col-md-6">
                @include('admin.pos.products')
            </div>
            <div class="col-md-6">

                <label><strong>Product Name:</strong></label>
                <select name="products" id="products" data-placeholder="Select Product"
                        class="form-control select2"></select>
            </div>
        </div>
        <div id="tables" class="row">
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

                {{--	@include('admin.order.products')--}}
            </div>
            <div class="col-md-5">

                @include('admin.order.payment')

            </div>

        </div>

    </form>
@endsection
@include('plugins.ajax')
@include('plugins.DataTables')
@include('plugins.select2')
@push('footer')

    <script>


        $(document).ready(function () {


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
                    data: {id: id, customer: '{{$customer->id}}'},
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


            refreshCart()
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
            let url = '{{route("order.add_to_cart", $customer->id) }}'
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
            let url = '{{route("order.add_to_cart", $customer->id) }}'
            let stock = $('.stockCart' + id).val()
            let taxable_price = $('.priceCart' + id).val()
            let price = $('.actualCart' + id).val()
            let purchase_price = $('.pPriceCart' + id).val()
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
                            url: '{{route('order.empty_cart', $customer->id)}}',
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
            let price = $('#price').val()
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
            @if($customer)
            $('#paid').val(0)
            @else
            $('#paid').val(round(parseFloat(total), 2))
            @endif
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
                            url: '{{route('order.delete_from_cart', $customer->id)}}',
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

        key('s', function () {
            posOrder.save('{{route('order.add',$customer->id)}}', false)
        })

        ui.$body.on('click', '[data-action="save"]', function (e) {
            e.preventDefault()
            posOrder.save('{{route('order.add',$customer->id)}}', true)
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

    </script>
@endpush()
