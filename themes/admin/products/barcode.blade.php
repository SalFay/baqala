@extends('admin.layouts.admin')

@section('page-title','Manage Products')
@section('heading','Manage Products')
@section('breadcrumbs', 'Products')

@section('content')
	<style>
     @media print {

         .hidden-print {
             display: none;
         }
     }
	</style>
	<div class="card" id="section-overview">
		<div
			class="card-header bg-teal-400 header-elements-inline">
			<h6 class="card-title">Product Barcode</h6>
		</div>

		<div class="card-body">
			<div class="row">
				@foreach($products as $product)
					<div class="col-md-3">

						<!-- Linked image -->
						<div class="card" style="padding: 10px;">

							<img src="data:image/png;base64,{{\Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($product->pid, 'C128')}}"
							     alt="barcode"/>
							<div class="card-body" style="padding-top: 2px;padding-bottom: 0px;">
								<h5 class="card-title text-center"
								    style="font-size: 10px;">{{$product->category->name . ' - ' . $product->name}}</h5>
							</div>
						</div>
						<!-- /linked image -->

					</div>
			@endforeach

			<!-- /overlay buttons -->

			</div>
		</div>
	</div>
@endSection

