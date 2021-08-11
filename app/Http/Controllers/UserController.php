<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
class UserController extends Controller
{
    public function update(Request $request)
    {
        $user = User::find(Auth::id());
        $validator = Validator::make($request->all(), [
            'name' => [ 'string', 'min:4'],
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'user_name' => ['required', 'min:4','max:20','unique:users,user_name,'.$user->id],
            'avatar' => 'dimensions:width=256,height=256' 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message'=>$validator->messages()
            ]);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->user_name = $request->user_name;
        if($request->hasFile('avatar'))
        {
            $file = $request->file('avatar');
            $namewithextension = $file->getClientOriginalName(); //Name with extension 'filename.jpg'
            $name = explode('.', $namewithextension)[0]; // Filename 'filename'
            $extension = $file->getClientOriginalExtension(); 
            $file_name = Str::slug($user->name).'.'.$extension;
            $request->file('avatar')->storeAs('public/avatar', $file_name);
            $user->avatar ='/public/storage/avatar/'.$file_name;
        }
        if ($user->save()) {
            return response()->json([
                'success' => true,
                'message'=>'Profile successfully updated',
                'data'=>$user->email
            ]);
        }
        
    }
}
