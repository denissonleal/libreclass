<?php namespace App\Http\Controllers;

class PeriodsController extends Controller
{
	private $user_id;

	public function __construct()
	{
		$id = session("user");
		if ($id == null || $id == "" ) {
			$this->user_id = false;
		}
		else {
			$this->user_id = decrypt($id);
		}
	}

	public function getIndex()
	{
		if ($this->user_id) {
			$user = User::find($this->user_id);
			$courses = Course::where("institution_id", $this->user_id)->whereStatus("E")->orderBy("name")->get();
			$listCourses = [];
			foreach ($courses as $course) {
				$listCourses[$course->id] = $course->name;
			}
			return view("social.periods", ["listCourses" => $listCourses, "user" => $user]);
		}
		else {
//      return redirect("/");
		}
	}

	public function anyList() {
		if ($this->user_id) {
			$periods = Period::where('course_id', request()->get('course_id'))->where('status', 'E')->get();
			if($periods) {
	      return view("social.periods.list", [ "periods" => $periods ]);
	    }
		}
	}

	public function anySave() {
		if ($this->user_id) {

			$period = new Period;
			if(request()->has('period_id')) {
				$period = Period::find(request()->get('period_id'));
			}
			$period->name = request()->get('name');
			$period->course_id = request()->get('course_id');
			// $period->progression_value = request()->get('progression_value');
			// dd(request()->all());
			$period->save();

			return Redirect::back()->with("success", "Período salvo com sucesso!");
		}
	}

	public function anyRead() {
		if ($this->user_id) {
			$period = Period::find(request()->get('period_id'));
			if($period) {
	      return ['period' => $period];
	    }
		}
	}

}
