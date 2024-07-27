@foreach($auth_logs as $log)
	<li class="timeline-item">
		@if($log->log_name === 'authentication')
			<span
				class="timeline-point timeline-point-{{ucfirst($log->description) === 'Logged-in' ? 'success' : 'danger'}} timeline-point-indicator"></span>
			<div class="timeline-event" style="min-height: 0 !important;">
				<div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">

					<h6>{{logCauserDetail($log->subject_id) ?  logCauserDetail($log->subject_id) : ''}} {{ucfirst($log->description)}} </h6>
					<span class="timeline-event-time">{{($log->created_at)->format( 'd-m-Y h:i A' )}}</span>
				</div>
			</div>
		@else
			<span class="timeline-point timeline-point-indicator"></span>
			<div class="timeline-event">
				<div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">

					@if(str_contains($log->log_name,'send'))
						<h6>{{logCauserDetail($log->causer_id) ?  logCauserDetail($log->causer_id) : ''}} {{ucwords(str_replace('-', ' ', $log->log_name))}}
							to {{$log->properties['sms'] ?? $log->properties['email']}} </h6>
					@else
						<h6>{{logCauserDetail($log->causer_id) ?  logCauserDetail($log->causer_id) : ''}}  {{ucfirst($log->description)}} {{ucwords(str_replace('-', ' ', $log->log_name))}} </h6>
					@endif
					<span class="timeline-event-time">{{($log->created_at)->format( 'd-m-Y h:i A' )}}</span>
				</div>
				<p>{{ucfirst($log->description)}}</p>
				<small>
					@if(isset($log->properties['attributes']))
						<ul>
							@foreach($log->properties['attributes'] as $key => $attr)
								@if($key === 'created_at' || $key === 'updated_at' || $key === 'deleted_at' || str_contains($key, '_id') || $key === 'id')
									@continue
								@endif
								@if($key === 'payment_info' && is_array(json_decode($attr, JSON_PRETTY_PRINT)))
									<li>Payment Info</li>
									<ol>

										@foreach(json_decode($attr, JSON_PRETTY_PRINT) as $k => $at)
											<li>{!! ucwords(str_replace(['-', '_'],' ', $k . ' : '. $at)) !!}</li>
										@endforeach
									</ol>
								@else
									<li>{!! ucwords(str_replace(['-', '_'],' ', $key . ' : '. $attr)) !!}</li>

								@endif
							@endforeach
						</ul>
					@endif
				</small>
			</div>
		@endif
	</li>


@endforeach