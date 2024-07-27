@extends('admin.layouts.admin')

@section('page-title','Manage Statement')
@section('heading','Manage Statement')
@section('breadcrumbs', 'Statement')

@section('content')
	<div class="card" id="section-overview">
		<div
			class="card-header bg-teal-400 header-elements-inline">
			<h6 class="card-title"> Statement of {{$vendor_name}}</h6>
		</div>

		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered table-striped table-hover">
					<thead>
					<tr>
						<th> Date</th>
						<th>Comment</th>
						<th>Debit</th>
						<th>Credit</th>
						<th>Remaining</th>
					</tr>
					</thead>
					<tbody>
     <?php $remaining = 0; ?>

					@foreach($statement as $st)
						<tr>
							<td>{{date('j F, Y, g:i A', strtotime($st->created_at))}}</td>
							<td>{{$st->comments}}</td>
							<td>{{$st->credit}}</td>
							<td>{{$st->debit}}</td>

							<td>    {{$remaining += $st->credit - $st->debit}}  </td>

						</tr>
					@endforeach
					<tr>
						<th colspan="4">Closing Balance</th>
						<td>{{$remaining}}</td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
@endsection

