<div class="row data-table-header">
    <div class="col-sm-3">
        <div class="input-group">
            <label for="dataTablesSearch" class="input-group-addon">Search</label>
            <input type="text" id="dataTablesSearch" class="form-control">
        </div>
    </div>
    <div class="col-sm-6">

    </div>
    <div class="col-sm-3">
        <div class="input-group">
            <label for="dataTablesPageLength" class="input-group-addon">Show</label>
            <select class="form-control" id="dataTablesPageLength">
                <option></option>
            </select>
        </div>
    </div>
</div>

@push('footer')
    <script>
        UI.$body.on('keyup', '#dataTablesSearch', function () {
            let el = $(this);
            clearTimeout(window.dataTablesSearch);
            window.dataTablesSearch = setTimeout(function () {
                let val = el.val();

                table.api().search(val).draw();
            }, 600);
        });
    </script>
@endpush
