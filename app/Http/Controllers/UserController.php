<?php

namespace App\Http\Controllers;

use App\Mail\EmailVerification;
use App\Mail\PasswordReset;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class UserController extends Controller
{
  public function login(Request $request){
    $username=trim($request->username);
    $password=trim($request->password);
    if ($username!=null || $username!=""  || $password!=null || $password!="") {
      $users=User::where('name', 'LIKE', $username)->get();
      if (count($users) != 0) {
        $counter=0;
        foreach ($users as $user) {
          if($user->password==$password){
            $counter++;
          }
        }
        if($counter>0){
          $name=$user->name;
          return view("home", ["name"=>$name, "notice"=>"Welcome back to ArthurCo."]);
        }else{
          $error="PASSWORD INCORRECT";
          return view("loginError", ["error"=>$error]);
        }
      }else{
        $error="USER NOT FOUND";
        return view("loginError", ["error"=>$error]);
      };
    }else{
      $error="PLEASE FILL IN ALL FIELDS";
      return view("loginError", ["error"=>$error]);
    }
    
    dump($password);
  }

  public function signup(Request $request){
    $username=trim($request->username);
    $email=trim($request->email);
    $password=trim($request->password);
    $repassword=trim($request->repassword);
    if($username!=null || $username!="" || $email!=null || $email!=""  || $password!=null || $password!="" || $repassword!=null || $repassword!=""){
      if ($password==$repassword) {
        $duplicteUser = User::where(function ($query) use ($username, $email, $password){
            $query->where('name', 'like', $username)
            ->orWhere('email', 'like', $email)
            ->orWhere('password', 'like', $password);
        })->get();
        if(count($duplicteUser)>0){
          $error="USER ALREADY EXISTS TRY LOGGING IN";
          return view("signupError", ["error"=>$error]);
        }else {
          $maildData = array(
              'username'     => $username,
              'email'     => $email,
              'password'     => $password,
          );
          // Mail::to($email)->send(new EmailVerification($maildData));
          $sentMail=Mail::to($email)->send(new EmailVerification($maildData));
          return view("signedUpScreen");
        }
      }else{
        $error="PASSWORDS DO NOT MATCH";
        return view("signupError", ["error"=>$error]);
      }
    }else{
      $error="PLEASE FILL IN ALL FIELDS";
      return view("signupError", ["error"=>$error]);
    }
  }

  public function verify($username,$email,$password){
    $user = User::create([
        'name' => $username,
        'email' => $email,
        'password' => $password,
        'email_verified_at' => Carbon::now(),
    ]);
    return view("home", ["name"=>$username, "notice"=>"Congratulations! You have signed up to ArthurCo."]);
  }

  public function reset(Request $request, $token){
    $password=$request->password;
    $repassword=$request->repassword;
    if($password!=null || $password!="" || $repassword!=null || $repassword!=""){
      if ($password==$repassword) {
        $passwordDouble=User::where('password', 'LIKE', $password)->get();
        if (count($passwordDouble)>0) {
          $error="PASSWORD ALREADY IN USE";
          return view("passwordResetError", ["error"=>$error, 'token' => $token, 'url' => URL::to('/')]);
        }else{
          $addToken=User::where('remember_token' , $token)->update(['password' => $password, 'remember_token' => ""]);
          $error="PASSWORD UPDATED";
          return view("loginError", ["error"=>$error, 'token' => $token, 'url' => URL::to('/')]);
        }
      }else{
        $error="PASSWORDS DO NOT MATCH";
        return view("passwordResetError", ["error"=>$error, 'token' => $token, 'url' => URL::to('/')]);
      }
    }else{
      $error="PLEASE FILL IN ALL FIELDS";
      return view("passwordResetError", ["error"=>$error, 'token' => $token, 'url' => URL::to('/')]);
    }
  }

  public function forgotPassword(Request $request){
    $email=$request->email;
    $users=User::where('email', 'LIKE', $email)->get();
    if (count($users)==0) {
      $error="Email not found!";
      return view("passwordForgotError", ["forgotError"=>$error]);
    }else{
      $token=Str::random(10);
      $addToken=User::where('email', $email)->update(['remember_token' => $token]);
      $sentMail=Mail::to($email)->send(new PasswordReset($token));
      return view("login");
    }
  }

  public function editProfile($name){
      $users=User::where('name', 'LIKE', $name)->get();
      foreach ($users as $user) {
        if (isset($user)) {
          return view("profile", ["name"=>$user->name, "email"=>$user->email, "password"=>$user->password, 'url' => URL::to('/')]);
        }
      }
  }

  public function updateProfile(Request $request){
    $name=$request->username;
    $email=$request->email;
    $password=$request->password;
    $update=User::where('name', $name)->update(['name' => $name, 'email' => $email, 'password' => $password]);
    return redirect('profileEdit/' . $name);
  }

  public function delete($name){
      $result=User::where('name','=',$name)->delete();
      return redirect('/');
  }

  public function search($email){
    $searchArr=array();
    $users=User::where('email','LIKE',$email)->get();
    foreach ($users as $user) {
      array_push($searchArr, $user);
    }
    return $searchArr;
}

}
