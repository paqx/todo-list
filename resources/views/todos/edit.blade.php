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
				<div class="card-header">Edit</div>
				<div class="card-body">
					<form action="{{ route('todos.update', $todo->id) }}" method="POST" enctype="multipart/form-data">
						@csrf
						@method ('PUT')
						<div class="mb-3">
							<label for="contents" class="form-label">What do you need to do?</label>
							<textarea class="form-control" name="contents" id="contents" rows="3">{{ $todo->contents }}</textarea>
						</div>
						@if ($todo->thumbnail)
						<div class="mb-3">
							<p>Current image:</p>
							<img src="{{ asset('storage/images/'.$todo->thumbnail) }}">
						</div>
						@endif
						<div class="mb-3">
							<label for="image" class="form-label">Upload new image:</label>
							<input type="file" class="form-control" name="image" id="image">
						</div>
						<div class="mb-3">
							<label for="tags" class="form-label">Tags (comma-separated):</label>
							<input type="text" class="form-control" name="tags" id="tags" placeholder="wine, fish, soup" value="{{ $tagNamesString }}">
							
						</div>
						<div class="row mb-3">
							<div class="col-md-6 d-grid">
								<button type="submit" class="btn btn-primary">Save changes</button>
							</div>
							<div class="col-md-6 d-grid">
								<a href="{{ url()->previous() }}" class="btn btn-secondary">Go back</a>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection