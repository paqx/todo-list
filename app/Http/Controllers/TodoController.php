<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\Tag;
use App\Models\TagTodo;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Image;
use Illuminate\Support\Facades\Storage;

class TodoController extends Controller
{
	/**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
		$request->validate([
			'tags' => 'nullable|array',
			'search' => 'nullable|max:255',
		]);

		try {
			$tagNamesArray = $request->input('tags');
			$searchQuery = $request->input('search');
			$userId = auth()->user()->id;

			if (isset($tagNamesArray)) {
				$tagIdsArray = Tag::whereIn('name', $tagNamesArray)
						->pluck('id')
						->toArray();

				if (count($tagNamesArray) != count($tagIdsArray)) {
					return back()
							->withErrors('Specified tags do not exist in the database.');
				}
			}

			if (isset($tagIdsArray)) {
				$todos = Todo::where('user_id', $userId)
						->whereHas('tags', function (Builder $query) use ($tagIdsArray) {
							$query->whereIn('tag_id', $tagIdsArray);
						}, '=', count($tagIdsArray))
						->orderBy('created_at', 'DESC')
						->paginate(10);
			} elseif (isset($searchQuery)) {
				$todos = Todo::where('user_id', $userId)
						->where('contents', 'LIKE', '%' . $searchQuery . '%')
						->orderBy('created_at', 'DESC')
						->paginate(10);
			} else {
				$todos = Todo::where('user_id', $userId)
						->orderBy('created_at', 'DESC')
						->paginate(10);
			}

			return view('todos.index')->with([
						'todos' => $todos->appends($tagNamesArray),
			]);
		} catch (Exception $ex) {
			return view('todos.index')->withErrors($ex->getMessage());
		}
	}

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
			'contents' => 'required|max:255',
			'image' => 'nullable|image|dimensions:min_width=150,min_height=150',
			'tags' => 'nullable|max:255',
		]);

		try {
			$todo = new Todo();

			if ($request->hasFile('image')) {
				$imageNamesArray = $this->saveImage($request);
				$todo->image = $imageNamesArray['image'];
				$todo->thumbnail = $imageNamesArray['thumbnail'];
			}

			$todo->user_id = auth()->user()->id;
			$todo->contents = $request->input('contents');
			$todo->save();

			$tagNamesString = $request->input('tags');

			if (isset($tagNamesString)) {
				$tagIdsArray = $this->parseTagNamesString($tagNamesString);
				$todo->tags()->attach($tagIdsArray);
			}

			return back()->with(
							'status',
							'New item has been successfully added to the list.'
			);
		} catch (Exception $ex) {
			return redirect(route('todos.index'))
							->withErrors($ex->getMessage());
		}
	}

    public function show(Todo $todo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function edit(Todo $todo)
    {
		try {
			$tagNamesArray = $todo->tags()->pluck('name')->toArray();
			$tagNamesString = '';
			$arrayLength = count($tagNamesArray);

			for ($i = 0; $i < $arrayLength; $i++) {
				if ($i == $arrayLength - 1) {
					$tagNamesString .= $tagNamesArray[$i];
				} else {
					$tagNamesString .= $tagNamesArray[$i] . ', ';
				}
			}

			return view('todos.edit')->with([
						'todo' => $todo,
						'tagNamesString' => $tagNamesString,
			]);
		} catch (Exception $ex) {
			return back()->withErrors($ex->getMessage());
		}
	}

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Todo $todo)
    {
		$request->validate([
			'contents' => 'required|max:255',
			'image' => 'nullable|image|dimensions:min_width=150,min_height=150',
			'tags' => 'nullable|max:255',
		]);

		try {
			$this->validateUser($todo->user_id);

			if ($request->hasFile('image')) {
				$this->removeImage($todo);
				$imageNamesArray = $this->saveImage($request);
				$todo->image = $imageNamesArray['image'];
				$todo->thumbnail = $imageNamesArray['thumbnail'];
			}

			$todo->user_id = auth()->user()->id;
			$todo->contents = $request->input('contents');
			$todo->update();

			$this->removeTags($todo);
			$todo->tags()->detach();
			$tagNamesString = $request->input('tags');

			if (isset($tagNamesString)) {
				$tagIdsArray = $this->parseTagNamesString($tagNamesString);
				$todo->tags()->attach($tagIdsArray);
			}

			return redirect(route('todos.index'))
				->with('status','New item has been successfully added to the list.');
		} catch (Exception $ex) {
			return back()->withErrors($ex->getMessage());
		}
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Todo $todo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Todo $todo)
    {
        try {
			$this->validateUser($todo->user_id);
			$this->removeTags($todo);
			$todo->tags()->detach();
			$this->removeImage($todo);
			$todo->delete();

			return redirect(route('todos.index'))
				->with('status', 'The item has been successfully deleted.');

		} catch (Exception $ex) {
			return back()->withErrors($ex->getMessage());
		}
    }

	private function validateUser($userId)
	{
		if ($userId != auth()->user()->id) {
			return back()
					->withErrors('You do not have permission to delete the specified item.');
		}
	}

	private function saveImage(Request $request): array
	{
		$name = time() . rand(11111, 99999);
		$ext = $request->file('image')->extension();

		$imageFullName = $name . '.' . $ext;
		$thumbnailFullName = $name . '_thumb.' . $ext;

		$request->file('image')->storeAs('images', $imageFullName, 'public');
		Image::make($request->file('image'))
			->fit(150, 150)
			->save(Storage::disk('public')->path('images') . '/' . $thumbnailFullName);

		$imageNamesArray = [
			'image' => $imageFullName,
			'thumbnail' => $thumbnailFullName,
		];

		return $imageNamesArray;
	}

	private function removeImage(Todo $todo): void
	{
		empty($todo->image) ?: Storage::disk('public')
								->delete('images/' . $todo->image);
		empty($todo->thumbnail) ?: Storage::disk('public')
								->delete('images/' . $todo->thumbnail);
	}

	private function parseTagNamesString(string $tagNamesString): array
	{
		$tagNamesArray = explode(',', str_replace(' ', '', $tagNamesString));
		$tagIdsArray = [];

		foreach ($tagNamesArray as $tagName) {
			$tag = Tag::where('user_id', auth()->user()->id)
					->where('name', $tagName)
					->first();

			if (empty($tag)) {
				$tag = new Tag();
				$tag->user_id = auth()->user()->id;
				$tag->name = $tagName;
				$tag->save();
			}

			array_push($tagIdsArray, $tag->id);
		}

		return $tagIdsArray;
	}

	private function removeTags(Todo $todo): void
	{
		foreach ($todo->tags()->get() as $tag) {
			$count = TagTodo::where('tag_id', $tag->id)
					->where('todo_id', '<>', $todo->id)
					->count();

			($count != 0) ?: Tag::destroy($tag->id);
		}
	}

}
