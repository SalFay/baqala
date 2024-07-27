<div class="row">

	@foreach($abilities as $file => $data)
		<div class="card col-md-6">
			<div
				class="card-header bg-teal-400 header-elements-inline">
				<h6 class="card-title">{{$data['title']}} <br/>
					<small>{{$data['description']}}</small></h6>
				<div class="header-elements">
					<div class="list-icons">

						<input
							type="checkbox"
							placeholder="Check All"
							class="fancy-checkbox selectall"
							data-toggle="{{$data['title']}}"
							style="width: 41px !important"
						/>

					</div>
				</div>
			</div>

			<div class="table-responsive ">
				<table class="table">
					<tbody>
					@foreach($data['permissions'] as $ability=>$permission)
						<tr>
							<td>
								{{$permission['title']}}<br/>
								<small class="text-muted text-black-50">{{$permission['description']}}</small>
							</td>
							<td style="text-align: center">

								<input type="checkbox"
								       class="fancy-checkbox"
								       data-section="{{$data['title']}}"
								       name="permissions[]"
								       value="{{$ability}}"
								       id="{{str_slug($ability)}}"
								@if(!empty($role_permissions))
									{{in_array($ability, $role_permissions, true)?'checked':''}}
									@endif
								>

							</td>
						</tr>
					@endforeach
					</tbody>

				</table>
			</div>

		</div>
	@endforeach

</div>
<style>
    .card {
        border: 0 !important;
    }

</style>
<script>

	$('.selectall').change(function (event) {

		let el = event.target
		let checked = el.checked
		console.log(el)
		let section = el.dataset.toggle
		let target = document.querySelectorAll('[data-section="' + section + '"]')
		for (let i = 0; i < target.length; i++) {
			target[i].checked = checked
		}

	})
</script>
