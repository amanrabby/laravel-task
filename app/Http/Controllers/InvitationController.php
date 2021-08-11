<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invitation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Mail;
use App\Mail\InvitationEmail;

class InvitationController extends Controller
{
    public function invite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message'=>$validator->messages()
            ]);
        }


        $object = Invitation::where('email',$request->email)->first();
        if (!$object) {
            $object = new Invitation();
            $object->email = $request->email;
            $object->save();
        }
        $code = base64_encode($request->email);
        try{
            Mail::to($request->email)->send(new InvitationEmail($code));

            return response()->json([
                'success' => true,
                'message'=>'Invitation successfully sent',
                'data'=>$request->email
            ]);
        }

        catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message'=>$e->getMessage()
            ]);
        }
    }
}
