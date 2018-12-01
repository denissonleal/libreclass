<?php namespace App\Http\Controllers;

class BindController extends Controller
{
	public function anyLink()
	{
		$user = decrypt(request()->get("user"));
		$discipline = decrypt(request()->get("discipline"));
		if ( request()->get("bind") == "true"){
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
		$teacher = decrypt(request()->get("teacher"));

		return view("modules.addTeacherDisciplineForm",
								["courses" => Course::where("institution_id", decrypt(session("user")))->get(),
								 "teacher" => $teacher]);
	}

}
