<?php

class OffersController extends \BaseController
{

  private $user_id;

  public function OffersController()
  {
    $id = Session::get("user");
    if ($id == null || $id == "") {
      $this->user_id = false;
    } else {
      $this->user_id = decrypt($id);
    }
  }

  public function getUser()
  {
    $user = decrypt(Input::get("u"));
    if ($user) {
      $user = User::find($user);
      return $user;
    } else {
      return Redirect::guest("/");
    }
  }

  public function getIndex()
  {
    if ($this->user_id) {
      $user = User::find($this->user_id);
      $classe = Classe::find(decrypt(Input::get("t")));
      $period = Period::find($classe->period_id);
      $course = Course::find($period->course_id);
      $offers = Offer::where("class_id", $classe->id)->get();

      foreach ($offers as $offer) {
        $teachers = [];
        $list = Lecture::where("offer_id", $offer->id)->get();
        foreach ($list as $value) {
          $teachers[] = base64_encode($value->user_id);
        }
        $offer->teachers = $teachers;
        if (isset($offer->offer_id) && !empty($offer->offer_id)) {
          $offer->offer = Offer::find($offer->offer_id);
        }
      }

      return View::make("offers.institution", [
        "course" => $course,
        "user" => $user,
        "offers" => $offers,
        "period" => $period,
        "classe" => $classe,
      ]);
    } else {
      return Redirect::guest("/login");
    }
  }

  public function getUnit($offer)
  {
    $offer = Offer::find(decrypt($offer));
    if ($this->user_id != $offer->getClass()->getPeriod()->getCourse()->institution_id) {
      return Redirect::to("/classes/offers?t=" . encrypt($offer->class_id))->with("error", "Você não tem permissão para criar unidade");
    }

    $old = Unit::where("offer_id", $offer->id)->orderBy("value", "desc")->first();

    $unit = new Unit;
    $unit->offer_id = $offer->id;

		if(!$old) {
			$unit->value = 1;
			$unit->calculation = 'A';
		}
		else {
			$unit->value = $old->value + 1;
			$unit->calculation = $old->calculation;
		}
    $unit->save();

		if($old) {
			$attends = Attend::where("idUnit", $old->id)->get();

			foreach ($attends as $attend) {
				$new = new Attend;
				$new->idUnit = $unit->id;
				$new->user_id = $attend->user_id;
				$new->save();
			}
		}


    return Redirect::to("/classes/offers?t=" . encrypt($offer->class_id))->with("success", "Unidade criada com sucesso!");
  }

  public function postTeacher()
  {
    $offer = Offer::find(decrypt(Input::get("offer")));
    $offer->classroom = Input::get("classroom");
    $offer->day_period = Input::get("day_period");
    $offer->maxlessons = Input::get("maxlessons");
    $offer->save();
    $lectures = $offer->getAllLectures();

    $teachers = [];
    if (Input::has("teachers")) {
      $teachers = Input::get("teachers");
      for ($i = 0; $i < count($teachers); $i++) {
        $teachers[$i] = base64_decode($teachers[$i]);
      }

    }

    foreach ($lectures as $lecture) {
      $find = array_search($lecture->user_id, $teachers);
      if ($find === false) {
        Lecture::where('offer_id', $offer->id)->where('user_id', $lecture->user_id)->delete();
      } else {
        unset($teachers[$find]);
      }

    }

    foreach ($teachers as $teacher) {
      $last = Lecture::where("user_id", $teacher)->orderBy("order", "desc")->first();
      $last = $last ? $last->order + 1 : 1;

      $lecture = new Lecture;
      $lecture->user_id = $teacher;
      $lecture->offer_id = $offer->id;
      $lecture->order = $last;
      $lecture->save();
    }

    return Redirect::guest(Input::get("prev"))->with("success", "Modificado com sucesso!");
  }

  public function postStatus()
  {
    $status = Input::get("status");
    $id = decrypt(Input::get("unit"));

    $unit = Unit::find($id);
    if (!strcmp($status, 'true')) {
      $unit->status = 'E';
    } else {
      $unit->status = 'D';
    }
    $unit->save();

    return "Status changed to " . $status . " / " . $id;
  }

  public function getStudents($offer)
  {
    if ($this->user_id) {
      $user = User::find($this->user_id);

      $info = DB::select("SELECT Courses.name as course, Periods.name as period, Classes.id as class_id, Classes.class as class
                          FROM Courses, Periods, Classes, Offers
                          WHERE Courses.id = Periods.course_id
                          AND Periods.id = Classes.period_id
                          AND Classes.id = Offers.class_id
                          AND Offers.id = " . decrypt($offer) . "
                          ");
      $students = DB::select("SELECT Users.name as name, Users.id as id, Attends.status as status
                              FROM Users, Attends, Units
                              WHERE Users.id=Attends.user_id
                              AND Attends.idUnit = Units.id
                              AND Units.offer_id = " . decrypt($offer) . " GROUP BY Users.id ORDER BY Users.name");

      return View::make("modules.liststudentsoffers", ["user" => $user, "info" => $info, "students" => $students, "offer" => $offer]);
    } else {
      return Redirect::guest("/");
    }
  }

  public function postStatusStudent()
  {

    //~ return Input::all();
    $offer = decrypt(Input::get("offer"));
    $student = decrypt(Input::get("student"));
    $units = Unit::where("offer_id", $offer)->get();

    if (Input::get("status") == 'M') {
      foreach ($units as $unit) {
        Attend::where('idUnit', $unit->id)->where('user_id', $student)->update(["status" => 'M']);
      }

    }

    if (Input::get("status") == 'D') {
      foreach ($units as $unit) {
        Attend::where('idUnit', $unit->id)->where('user_id', $student)->update(["status" => 'D']);
      }

    }

    if (Input::get("status") == 'T') {
      foreach ($units as $unit) {
        Attend::where('idUnit', $unit->id)->where('user_id', $student)->update(["status" => 'T']);
      }

    }

    if (Input::get("status") == 'R') {
      foreach ($units as $unit) {
        Attend::where("idUnit", $unit->id)->where("user_id", $student)->delete();
      }

      return Redirect::back()->with("success", "Aluno removido com sucesso");
    }
    return Redirect::back()->with("success", "Status atualizado com sucesso");
  }

  public function anyDeleteLastUnit($offer)
  {
    $offer = Offer::find(decrypt($offer));

    $unit = Unit::where('offer_id', $offer->id)->orderBy('value', 'desc')->first();
    $unit->delete();

    return Redirect::to("/classes/offers?t=" . encrypt($offer->class_id))->with("success", "Unidade deletada com sucesso!");
  }

	public function postOffersGrouped() {
		if(!Input::has('group_id')) {
			return ['status'=> 0];
		}

		$groupId = Input::get('group_id');
		$offers = Offer::where('offer_id', decrypt($groupId))->where('grouping', 'S')->get();
		foreach ($offers as $key => $offer) {
			$offer->id = encrypt($offer->id);
			$offer->_discipline = $offer->discipline;
		}
		return ['status' => 1, 'offers' => $offers];
	}

}
