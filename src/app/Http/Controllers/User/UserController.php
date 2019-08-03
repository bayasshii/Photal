<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Socialite;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function updateUser(Request $request) {
        $token = $request->session()->get('github_token', null);
        $user = Socialite::driver('github')->userFromToken($token);
        
        // user_idを振る
        $user_id = mt_rand();
        $user->user_id = $user_id;
        $user->save();
        
        DB::update('update public.user set name = ?, comment = ? where github_id = ?', [$request->input('name'), $request->input('comment'), $user->user['login']]);
        return redirect('/photal');
    }    
}