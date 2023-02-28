@extends('layouts.app')

@section('content')
<div class="container">
	@if ($errors->any() || session('status'))
	<div class="row justify-content-center">
		<div class="col-md-12">
			@if ($errors->any())
			<div class="alert alert-danger">
				<ul class="mb-0">
					@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
			@endif
			@if (session('status'))
			<div class="alert alert-success" role="alert">
				{{ session('status') }}
			</div>
			@endif
		</div>
	</div>
	@endif
    <div class="row">
		 <div class="col-md-4">
            <div class="card mb-4">
				<div class="card-header">Add an Item on Your List</div>
				<div class="card-body">
					<form action="{{ route('todos.store') }}" method="POST" enctype="multipart/form-data">
						@csrf
						<div class="mb-3">
							<label for="contents" class="form-label">What do you need to do?</label>
							<textarea class="form-control" name="contents" id="contents" rows="3">{{ old('contents') }}</textarea>
						</div>
						<div class="mb-3">
							<label for="image" class="form-label">Image:</label>
							<input type="file" class="form-control" name="image" id="image">
						</div>
						<div class="mb-3">
							<label for="tags" class="form-label">Tags (comma-separated):</label>
							<input type="text" class="form-control" name="tags" id="tags" placeholder="wine, fish, soup" value="{{ old('tags') }}">
						</div>
						<div class="d-grid">
							<button type="submit" class="btn btn-primary">Add to the list</button>
						</div>
					</form>
				</div>
			</div>
			<div class="card">
				<div class="card-header">Share my To-Do List</div>
				<div class="card-body">
					<form action="{{ route('share.store') }}" method="POST">
						@csrf
						<div class="mb-3">
							<label for="email" class="form-label">Email of the user you want to share this list with:</label>
							<input type="email" class="form-control" name="email" id="email" placeholder="mail@example.com" value="{{ old('email') }}">
						</div>
						<div class="mb-3">
							<p class="mb-1">Permissions:</p>
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="permissions" id="permissionsRead" value="read" checked>
								<label class="form-check-label" for="permissionsRead">
									Read
								</label>
							</div>
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="permissions" id="permissionsWrite" value="readwrite">
								<label class="form-check-label" for="permissionsWrite">
									Read & Write
								</label>
							</div>
						</div>
						<div class="d-grid">
							<button type="submit" class="btn btn-primary">Share</button>
						</div>
					</form>
				</div>
			</div>
		 </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Your Personal To-Do List</div>
                <div class="card-body">
					@if (request()->get('tags'))
					<p>
						Selected tags: 
						@foreach (request()->get('tags') as $tag)
							@if ($loop->last)
								<b>{{ $tag }}</b>.
							@else
								<b>{{ $tag }}</b>, 
							@endif
						@endforeach
						Click <a href="{{ route('todos.index') }}">here</a> to remove the filters.
					</p>
					@elseif (request()->get('search'))
					<p>
						Search results for <strong>{{ request()->get('search') }}</strong>.
						Click <a href="{{ route('todos.index') }}">here</a> to start a new search.
					</p>
					@else
					<form class="mx-auto w-50 mb-3" action="{{ route('todos.index') }}" method="GET">
						<div class="input-group">
							<input type="text" class="form-control" name="search" id="search" placeholder="Search">
							<button class="btn btn-primary" type="submit">Search</button>
						</div>
					</form>	
					@endif
					@if ($todos->isEmpty())
						<p>There's nothing on your to-do list yet :-(</p>
					@else
						<table class="table">
							<thead>
								<tr>
									<th>Images</th>
									<th>Contents</th>
									<th>Tags</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($todos as $todo)
								<tr>
									<td>
										@if (isset($todo->thumbnail))
										<a href="{{ asset('storage/images/'.$todo->image) }}" target="_blank">
											<img src="{{ asset('storage/images/'.$todo->thumbnail) }}">
										</a>
										@endif
									</td>
									<td>{{ $todo->contents }}</td>
									<td>
										@foreach ($todo->tags()->get() as $tag)
										<a href="{{ route('todos.index', [
											'tags' => request()->get('tags'),
											'tags[]' => $tag->name
										]) }}" class="btn btn-sm btn-outline-secondary 
										@if (request()->get('tags') && in_array($tag->name, request()->get('tags'))) 
										disabled 
										@endif
										">{{ $tag->name }}</a>
										@endforeach
									</td>
									<td>
										<form action="{{ route('todos.destroy', $todo->id) }}" method="POST">
											@method ('DELETE')
											@csrf
											<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
												<a href="{{ route('todos.edit', $todo->id) }}" class="btn btn-warning">Edit</a>
												<button type="submit" class="btn btn-danger">Delete</button>
											</div>
										</form>
									</td>
								</tr>
								@endforeach
							</tbody>
						</table>
						<div class="d-flex justify-content-center">
							{{ $todos->links() }}
						</div>
					@endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
