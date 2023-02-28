@extends('layouts.app')

@section('content')
<div class="container">
	@if ($errors->any() || session('status'))
	<div class="row justify-content-center">
		<div class="col-md-8">
			@if ($errors->any())
			<div class="alert alert-danger">
				<ul class="mb-0">
					@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
			@endif
		</div>
	</div>
	@endif
	<div class="row justify-content-center">
		 <div class="col-md-8">
            <div class="card">
				<div class="card-header">Who shared their lists with you</div>
				<div class="card-body">
					@if ($givers->isEmpty())
					<p>Nobody has shared their list with you yet.</p>
					@else
					<ul>
						@foreach ($givers as $giver)
						<li>
							<a href="{{ route('share.show', $giver->id) }}">
								{{ $giver->email }}
							</a>
						</li>
						@endforeach
					</ul>
					@endif
				</div>
			</div>
		</div>
	</div>
</div>
@endsection