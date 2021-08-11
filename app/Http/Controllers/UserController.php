<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function update(Request $request)
    {
        $user = User::find(Auth::id());
        $validator = Validator::make($request->all(), [
            'name' => [ 'string', 'min:4'],
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'user_name' => ['required', 'min:4','max:20'],
            'avatar' => 'dimensions:width=256,height=256'
            
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message'=>$validator->messages()
            ]);
        }


        return response()->json([
            'success' => true,
            'message'=>'Profile successfully updated',
            'data'=>$user->email
        ]);
    }
}
