<?php namespace App\Http\Controllers;

class BindController extends Controller
{
	public function anyLink()
	{
		$user = decrypt(Input::get("user"));
		$discipline = decrypt(Input::get("discipline"));
		if ( Input::get("bind") == "true"){
			$bind = new Bind;
			$bind->user_id = $user;
			$bind->discipline_id = $discipline;
			$bind->save();
			return "new";
		}
		else
			Bind::where("user_id", $user)->where("discipline_id", $discipline)->delete();

		return "del";
	}

	public function anyList()
	{
		$teacher = decrypt(Input::get("teacher"));

		return view("modules.addTeacherDisciplineForm",
								["courses" => Course::where("institution_id", decrypt(Session::get("user")))->get(),
								 "teacher" => $teacher]);
	}

}
