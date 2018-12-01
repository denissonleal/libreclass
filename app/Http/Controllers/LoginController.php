<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Hash;
use App\User;

class LoginController extends Controller
{
	public function index()
	{
		if (auth()->guest()) {
			return view('user.login');
		}
		return redirect('/');
	}

	public function login(Request $request)
	{
		$user = User::whereEmail($request->email)->first();

		if ($user and Hash::check($request->password, $user->password)) {
			if ( $user->cadastre == "W" ) {
				// $url = url("/check/") . "/" . encrypt($user->id);
				// Mail::send('email.welcome', ["url" => $url, "name" => $user->name ], function($message)
				// {
				//   $user = User::whereEmail(request()->get("email"))->first();
				//   $message->to( $user->email, $user->name )
				//           ->subject("Seja bem-vindo");
				// });
				return back()
					->with('error', "O email <b>$request->email</b> ainda não foi validado.")
					->withInput();
			} else {
				if ( $user->type == "M" || $user->type == "N" ) {
					$user->type = "P";
					$user->save();
				}
				auth()->login($user);
				return redirect('/');
			}
		} else {
			return back()
				->with('error', 'Login ou senha incorretos.')
				->withInput();
		}
	}

	public function logout()
	{
		auth()->logout();

		return redirect('/');
	}
}
