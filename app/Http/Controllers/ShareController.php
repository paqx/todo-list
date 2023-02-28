<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Permission;

class ShareController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $permissions = Permission::where('taker_id', auth()->user()->id);
		$giverIds = $permissions->pluck('giver_id')->toArray();
		$givers = User::whereIn('id', $giverIds)->get();
		
		return view('share.index')->with([
			'givers' => $givers
		]);
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
			'email' => 'required|email|exists:users',
			'permissions' => 'required'
		]);

		try {
			$taker = User::where('email', $request->input('email'))
					->first();

			if ($taker->id == auth()->user()->id) {
				return back()
					->withErrors('You are trying to give access to your own to-do list to yourself.');
			}

			$permission = new Permission();
			$permission->giver_id = auth()->user()->id;
			$permission->taker_id = $taker->id;
			
			$permissions = $request->input('permissions');
			
			if ($permissions == 'read') {
				$permission->read = true;
			} elseif ($permissions == 'readwrite') {
				$permission->read = $permission->write = true;
			}
			
			$permission->save();
			
			return back()->with('status','You successfully shared your list.');
					
		} catch (Exception $ex) {
			return back()->withErrors($ex->getMessage());
		}
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
