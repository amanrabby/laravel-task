<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Mail;
use App\Mail\ConfirmationEmail;
class RegistrationController extends Controller
{
    public function resend(Request $request)
    {
        $email=base64_decode($request->token);
        $exist_user = User::where(['email'=>$email,'verified'=>1])->first();
        if ($exist_user) {
            return response()->json([
                'success' => false,
                'message'=>'Email already exists',
                'data'=>$email
            ]);
        }
        $validator = Validator::make($request->all(), [
            'token'=>['required']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message'=>$validator->messages()
            ]);
        }

        $otp = random_int(100000, 999999);
        $user = User::where(['email'=>$email,'verified'=>0])->first();
        if ($user) {
            $user->pin = $otp;
            $user->save();
            try{
                Mail::to($email)->send(new ConfirmationEmail($otp));
                return response()->json([
                    'success' => true,
                    'message'=>'6 digit pin sent successfully',
                    'data'=>$email
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
    public function registration(Request $request)
    {
        $email = base64_decode($request->token);

        $exist_user = User::where(['email'=>$email])->first();
        if ($exist_user) {
            return response()->json([
                'success' => false,
                'message'=>'Email already exists',
                'data'=>$email
            ]);
        }
        $validator = Validator::make($request->all(), [
            'token'=>['required'],
            'user_name' => ['required', 'min:4','max:20',Rule::unique('users', 'user_name')],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message'=>$validator->messages()
            ]);
        }
        else
        {

            $otp = random_int(100000, 999999);

            $object = new User();
            $object->email = $email;
            $object->user_name = $request->user_name;
            $object->pin = $otp;
            $object->password = Hash::make($request->password);
            
            if ($object->save()) {
                try{
                    Mail::to($email)->send(new ConfirmationEmail($otp));
                    return response()->json([
                        'success' => true,
                        'message'=>'6 digit pin sent successfully',
                        'data'=>$email
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
    }

 

    public function confirmation(Request $request)
    {
        $pin = $request->pin;

        $validator = Validator::make($request->all(), [
            'pin'=>['required','min:6','max:6']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message'=>$validator->messages()
            ]);
        }

        $user = User::where(['pin'=>$pin])->first();
        if ($user) {
            $user->verified = 1;
            $user->pin = NULL;
            $user->register_at = date('Y-m-d H:i:s');
            $user->save();

            $inv_object = Invitation::where('email',$user->email)->first();
            $inv_object->status=1;
            $inv_object->save();

            return response()->json([
                'success' => true,
                'message'=>'User registration successfully completed.',
                'data'=>$user->email
            ]);
            
        }
        else
        {
            return response()->json([
                'success' => false,
                'message'=>'Incorrect Pin'
            ]);
        }
    }
}
