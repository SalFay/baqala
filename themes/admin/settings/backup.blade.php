@extends('admin.layouts.admin')

@section('page-title','Manage Backup')
@section('heading','Manage Backup')
@section('breadcrumbs', 'Backup')
@push('head')
	<style>
     .overlay {
         display: none;
         position: fixed;
         width: 100%;
         height: 100%;
         top: 0;
         left: 0;
         z-index: 999;
         background: rgba(255, 255, 255, 0.8) url("{{asset('assets/loading.gif')}}") center no-repeat;
     }

     /* Turn off scrollbar when body element has the loading class */
     .content.loading {
         overflow: hidden;
     }

     /* Make spinner image visible when body element has the loading class */
     .content.loading .overlay {
         display: block;
     }
	</style>
@endpush
@section('content')

	<div class="card">
		<div
			class="card-header bg-teal-400 header-elements-inline">
			<h6 class="card-title">Backup</h6>
			<div class="header-elements">
				<div class="list-icons">
					<a href="#" data-action="{{route('backup')}}" id="backup" class="btn btn-success btn-xs">
						<i class="fas fa-database"></i>
						<span>Backup Manually</span>
					</a>
				</div>
			</div>
		</div>

		<div class="card-body">
			<div class="overlay"></div>
			<div class="table-responsive">
				<table class="table table-bordered" id="table-signature">
					<thead>
					<tr>

						<th>
							File
						</th>
						<th>
							Action
						</th>
					</tr>
					</thead>
					<tbody>
					@foreach($files as $file)
						<tr>
							<td>{{$file['filename']}}</td>
							<td>
								<a href="{{ route('setup.backups.download',$file['basename']) }}"
								   class="btn btn-success"><i
										class="fas fa-download"></i></a> &nbsp;
								<a href="{{ route('setup.backups.delete',$file['basename']) }}"
								   class="btn btn-danger"><i
										class="fas fa-trash"></i></a>
							</td>
						</tr>

					@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
	@push('footer')

		<script>

		$('#backup').on('click', function () {
			$.ajax({
				url: $(this).data('action'),
				type: 'get',
				success: function (res) {
					ui.successMessage(res)
					return true
				},
				error: function (res) {
					ui.ajaxError(res, 0)
				}
			})
		})

		$(document).ajaxStart(function () {
			$('.content').addClass('loading')
		})
		$(document).ajaxComplete(function () {
			$('.content').removeClass('loading')
		})
		</script>
	@endpush
@endsection

