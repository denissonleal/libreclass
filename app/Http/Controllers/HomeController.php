<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Hash;
use App\User;

class HomeController extends Controller
{
	public function index()
	{
		if (auth()->guest()) {
			return view('home');
		}

		return view('social.home');
	}
}
