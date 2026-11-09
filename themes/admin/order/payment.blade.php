<div class="card border-dark">
    <div class="card-header bg-dark text-white header-elements-inline">
        <h6 class="card-title">Payment Method</h6>
        <div class="header-elements">
            <button type="button" style="float:right;bottom: 5px;" data-action="save" id="saveBtn"
                    class="btn btn-success">
                Save
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="form-group row">
            <label for="date" class="col-sm-5 col-form-label">Default Printer:</label>
            <div class="col-sm-7">
                @php($printer = option('printer'))
                <select name="printer" id="printer" data-placeholder="Select Printer" class="form-control"
                        data-module="select2">
                    <option value="Thermal Arabic New" {!! $printer === 'Thermal Arabic New'?' selected':'' !!}>Thermal
                        Arabic New
                    </option>
                    <option value="Thermal Eng" {!! $printer === 'Thermal Eng'?' selected':'' !!}>Thermal English
                    </option>
                </select>
            </div>
        </div>

        <div class="form-group row">
            <label for="date" class="col-sm-5 col-form-label">Customer Name:</label>
            <div class="col-sm-7">
                <input type="text" name="customer_name" value="{{$customer->full_name ?? ' '}}" id="customer_name"
                       required
                       class="form-control"/>
            </div>
        </div>
        <div class="form-group row">
            <label for="date" class="col-sm-5 col-form-label">Cashier Name:</label>
            <div class="col-sm-7">
                <input type="text" name="cashier_name" value="" id="cashier_name" required class="form-control"/>
            </div>
        </div>
        <div class="form-group row">
            <label for="date" class="col-sm-5 col-form-label">Date:</label>
            <div class="col-sm-7">
                <input type="text" name="date" value="{{date('Y-m-d')}}" id="date" required autocomplete="off" class="form-control"/>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-5 col-form-label">Total:</label>
            <div class="col-sm-7">
                <input type="number" name="price" id="price" readonly class="form-control" placeholder="Total"/>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-5 col-form-label">Delivery Charges:</label>
            <div class="col-sm-7">
                <input id="delivery" value="0" onblur="proceed()" type="text"
                       name="delivery_charges" class="form-control">
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-5 col-form-label">Discount Type:</label>
            <div class="col-sm-7">
                <select onchange="proceed()" id="disc" name="dis"
                        data-placeholder="Select Discount" class="form-control"
                        data-module="select2">
                    <option value="rupee"> Discount by Rs.</option>
                    <option value="per"> Discount by %</option>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-5 col-form-label">Discount:</label>
            <div class="col-sm-7">
                <input id="discount" value="0" onblur="proceed()" type="number"
                       name="discount"
                       class="form-control">
            </div>
        </div>

        {{--<label class="col-sm-5 col-form-label">Vat Amount:</label>
        <input type="text" name="vatAmount" id="vatAmount" value="{{option('vat_amount')}}%" readonly class="form-control"
                     placeholder="Net Amount"/>--}}

        <div class="form-group row">
            <label class="col-sm-5 col-form-label">Net Amount:</label>
            <div class="col-sm-7">
                <input type="number" name="netAmount" id="netAmount" readonly class="form-control"
                       placeholder="Net Amount"/>

            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-5 col-form-label"
                   for="status">Payment Method:</label>
            <div class="col-sm-7">
                <select name="payment_id" id="payments" data-placeholder="Select Payment Type"
                        class="form-control select2"></select>

            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-5 col-form-label">Paid:</label>
            <div class="col-sm-7">
                <input type="number" name="paid" id="paid" onchange="changed()" value="0" class="form-control"
                       placeholder="Enter Paid Amount"/>

            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-5 col-form-label">Change:</label>
            <div class="col-sm-7">
                <input type="number" name="change" id="change" value="0" class="form-control"/>


            </div>

        </div>
    </div>
</div>
@push('head')
    <style>
        .form-group {
            margin-bottom: 0.25rem !important;
        }
    </style>
@endpush


@push('head')
    <link rel="stylesheet" href="{{asset('plugins/js/pickers/flatpickr/flatpickr.min.css')}}">
@endpush

@push('footer')
    <script src="{{asset('plugins/js/pickers/flatpickr/flatpickr.min.js')}}"></script>
    <script>
        // One save routine for both POS screens (Save button and "S" key).
        // - Ignores repeat presses while a save is in flight or its dialog is open
        //   (a held/double "S" made duplicate orders).
        // - Asks first when the sale date isn't today, so a mistyped date is noticed.
        window.posOrder = (function () {
            let busy = false
            const DISPLAY = 'd-M-Y' // 09-Nov-2026
            // Shows 09-Nov-2026 whatever the PC's region is; still submits 2026-11-09.
            const picker = flatpickr('#date', {dateFormat: 'Y-m-d', altInput: true, altFormat: DISPLAY})

            function today() {
                return flatpickr.formatDate(new Date(), 'Y-m-d')
            }

            function pretty(ymd) {
                return flatpickr.formatDate(flatpickr.parseDate(ymd, 'Y-m-d'), DISPLAY)
            }

            // Coming Back from the receipt, the browser restores the old form values, which
            // carried one wrong date into every following sale. Always start from today.
            // The restored page also keeps `busy` from the save that opened the receipt.
            window.addEventListener('pageshow', function () {
                picker.setDate(today())
                busy = false
            })

            function cartProducts() {
                const products = []
                $('#table-cart tr').each(function (row, tr) {
                    if ($(tr).find('td:eq(0)').text() === '') {
                        return
                    }
                    products.push({
                        'id': $(tr).find('td:eq(0)').text(),
                        'sale_price': $(tr).find(':input').eq(0).val(),
                        'stock': $(tr).find('td:eq(2)').find('input').val(),
                        'taxable_price': $(tr).find('td:eq(3)').find('input').val(),
                        'purchase_price': $(tr).find('input[type=hidden]').val(),
                    })
                })
                return products
            }

            function send(url, products) {
                busy = true
                $.ajax({
                    url: url,
                    type: 'post',
                    dataType: 'json',
                    data: {
                        products: products,
                        sub_total: $('#price').val(),
                        total: $('#netAmount').val(),
                        discount_type: $('#disc').val(),
                        discount: $('#discount').val(),
                        date: $('#date').val(),
                        payment_id: $('#payments').val(),
                        payment: $('#price').val(),
                        delivery_charges: $('#delivery').val(),
                        amount: $('#paid').val(),
                        payment_type: 'Cash',
                        customer_name: $('#customer_name').val(),
                        cashier_name: $('#cashier_name').val(),
                        printer: $('#printer').val()
                    },
                    success: function (res) {
                        if (res.status === 'ok') {
                            $('#discount').val(0)
                            $('#delivery').val(0)
                            ui.successMessage(res.message)
                            refreshCart()
                            window.location = res.url // stay busy until the receipt opens
                            return
                        }
                        busy = false
                        ui.errorMessage(res.message)
                    },
                    error: function (res) {
                        busy = false
                        ui.ajaxError(res)
                    }
                })
            }

            function save(url, askFirst) {
                if (busy) {
                    return
                }
                const products = cartProducts()
                if (products.length === 0) {
                    ui.errorMessage('Enter Stock to Proceed')
                    return
                }

                const date = $('#date').val()
                const notToday = date !== today()
                if (!askFirst && !notToday) {
                    send(url, products)
                    return
                }

                busy = true // one dialog at a time
                $.confirm({
                    title: notToday ? 'Sale date is not today' : 'Are you sure?',
                    content: notToday
                        ? 'This sale will be saved on <strong>' + pretty(date) + '</strong>, but today is <strong>' + pretty(today()) + '</strong>.<br>Save it with this date?'
                        : 'You won\'t be able to revert this!',
                    type: notToday ? 'red' : 'warning',
                    buttons: {
                        confirm: function () {
                            send(url, products)
                        },
                        cancel: function () {
                            busy = false
                        }
                    }
                })
            }

            return {save: save}
        })()
    </script>
@endpush
