<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tag;
use App\Models\Todo;

class TagTodo extends Model
{
    use HasFactory;
	
	protected $table = 'tag_todo';
	
	public function tags() 
	{
		return $this->hasMany(Tag::class);
	}
	
	public function todos() 
	{
		return $this->hasMany(Todo::class);
	}
}
