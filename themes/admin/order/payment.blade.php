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
                <input type="date" name="date" value="{{date('Y-m-d')}}" id="date" required class="form-control"/>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-5 col-form-label">Total:</label>
            <div class="col-sm-7">
                <input type="text" name="price" id="price" readonly class="form-control" placeholder="Total"/>
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
                <input id="discount" value="0" onblur="proceed()" type="text"
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
                <input type="text" name="netAmount" id="netAmount" readonly class="form-control"
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
                <input type="text" name="paid" id="paid" onchange="changed()" value="0" class="form-control"
                       placeholder="Enter Paid Amount"/>

            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-5 col-form-label">Change:</label>
            <div class="col-sm-7">
                <input type="text" name="change" id="change" value="0" class="form-control"/>


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

