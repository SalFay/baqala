@extends('admin.layouts.admin')

@section('page-title', 'Product Sales Report')
@section('heading', 'Product Sales Report')
@section('breadcrumbs', 'Product Sales')

@section('content')
    <div class="card" id="section-overview">
        <div class="card-header bg-teal-400 header-elements-inline">
            <h6 class="card-title">Product Sales Report</h6>
        </div>

        <div class="card-body">
            <!-- Date Range Filter Form -->
            <form action="{{ route('reports.product-sold') }}" method="GET" class="mb-4" id="filter-form">
                @csrf
                <div class="customizer-styling-direction px-2 row">
                    <div class="col-md-8">
                        <input type="text" id="dataRange" name="date_range"
                               class="form-control flatpickr-range flatpickr-input active"
                               placeholder="From Date to Date Range" readonly="readonly">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" id="filter-range" class="btn btn-sm btn-primary">Filter</button>
                        &nbsp; &nbsp; &nbsp;
                        <button type="button" id="filter-refresh" class="btn btn-sm btn-secondary">Reset</button>
                    </div>
                </div>
            </form>

            <!-- Product Sales Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity Sold</th>
                        <th>Sale Price</th>
                        <th>Total Sales</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td>{{ $product['name'] }}</td>
                            <td>{{ $product['quantity_sold'] }}</td>
                            <td>{{ $product['sale_price'] }}</td>
                            <td>{{ number_format($product['total_sales'], 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr>
                        <th>Total</th>
                        <th>{{ $totalQuantitySold }}</th>
                        <th>{{ number_format($totalSales, 2) }}</th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection

@include('plugins.date-picker')

@push('footer')
    <script>
        // Initialize date range picker
        $('#dataRange').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear',
                format: 'YYYY-MM-DD',
                separator: ' - ' // Ensure the separator is a hyphen
            }
        });

        // Update the input field when a date range is selected
        $('#dataRange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        });

        // Clear the input field when the date range is cleared
        $('#dataRange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        // Handle reset button click
        $('#filter-refresh').on('click', function() {
            $('#dataRange').val(''); // Clear the date range input
            $('#filter-form').submit(); // Submit the form to reset the filter
        });
    </script>
@endpush
