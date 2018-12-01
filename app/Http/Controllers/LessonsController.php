<?php namespace App\Http\Controllers;

class LessonsController extends Controller
{
  private $user_id;

  public function LessonsController()
  {
    $id = session("user");
    if ($id == null || $id == "") {
      $this->user_id = false;
    } else {
      $this->user_id = decrypt($id);
    }

  }

  public function getIndex()
  {
    if ($this->user_id) {
      $user = User::find($this->user_id);
      $lesson = Lesson::find(decrypt(Input::get("l")));

      $students = DB::select("SELECT Users.name AS name, Attends.id AS attend_id, Frequencies.value AS value, Units.offer_id, Attends.user_id, Units.id AS unit_id
                                FROM Frequencies, Attends, Users, Units
                                WHERE Frequencies.attend_id=Attends.id AND
																			Attends.status != 'T' AND
                                      Attends.user_id=Users.id AND
                                      Frequencies.lesson_id=? AND
                                      Attends.unit_id=Units.id
                                ORDER BY Users.name", [$lesson->id]);

			//Obtém todas a aulas da oferta da aula para calcular atestados;

      foreach ($students as $student) {
				//Obtém os atestados
				$allLessons = Unit::find($student->unit_id)->getOffer()->lessons();
				$attests = Attest::where('idStudent',$student->user_id)->get();
				$qtdAttests = 0;
				foreach($attests as $attest) {
					$attest->dateFinish = date('Y-m-d', strtotime($attest->date. '+ '. ($attest->days - 1) .' days'));

					//If true, aluno possui um atestado para o dia da aula.
					if (($lesson->date >= $attest->date) && ($lesson->date <= $attest->dateFinish))
					{
						$student->attest = true;
					}

					foreach($allLessons as $tmpLesson) {
						if (($tmpLesson->date >= $attest->date) && ($tmpLesson->date <= $attest->dateFinish))
						{
							$qtdAttests++;
						}
					}
				}

        $frequency = DB::select("SELECT Offers.maxlessons, COUNT(*) as qtd "
                                  . "FROM Offers, Units, Attends, Frequencies "
                                  . "WHERE Offers.id=? AND Offers.id=Units.offer_id AND Units.id=Attends.unit_id "
                                    . "AND Attends.user_id=? AND Attends.id=Frequencies.attend_id AND Frequencies.value='F'",
                                [$student->offer_id, $student->user_id])[0];
        $student->maxlessons = $frequency->maxlessons;
        $student->qtd = $frequency->qtd - $qtdAttests;
      }
      return view("modules.lessons", ["user" => $user, "lesson" => $lesson, "students" => $students]);
    } else {
      return redirect("/");
    }
  }

  public function anyNew()
  {
    $unit = Unit::find(decrypt(Input::get("unit")));

		$date = date("Y-m-d");
		if(Input::has("date-year") && Input::has("date-month") && Input::has("date-day")) {
			$date = Input::get("date-year") . "-" . Input::get("date-month") . "-" . Input::get("date-day");
		}

    $lesson = new Lesson;
    $lesson->unit_id = $unit->id;
    $lesson->date = $date;
    $lesson->title = "Sem título";
    $lesson->save();
    $lessons_count = count(Lesson::where('unit_id', $unit->id)->get());

    $attends = Attend::where("unit_id", $unit->id)->get();
    foreach ($attends as $attend) {
      $frequency = new Frequency;
      $frequency->attend_id = $attend->id;
      $frequency->lesson_id = $lesson->id;
      $frequency->value = "P";
      $frequency->save();
    }

    if ($unit->offer->grouping == 'M') {
			if(Input::has('slaves')) {
				$slaves = [];
				foreach (Input::get('slaves') as $key => $s) {
					$slaves[] = (int) decrypt($s);
				}
				$offers = Offer::whereIn('id', $slaves)->get();
			}
			else {
				$offers = $unit->offer->slaves;
			}

      foreach ($offers as $offer) {
        $unit_slave = $offer->units()->where('value', $unit->value)->first();
        $lessons_count_slave = count(Lesson::where('unit_id', $unit_slave->id)->get());
        if ($lessons_count_slave >= $lessons_count) {
          continue;
        }
        $lesson_slave = new Lesson;
        $lesson_slave->unit_id = $unit_slave->id;
        $lesson_slave->date = Input::get("date-year") . "-" . Input::get("date-month") . "-" . Input::get("date-day");
        $lesson_slave->title = $lesson->title;
        Log::info('Lesson Slave', [$lesson_slave]);
        $lesson_slave->save();

        $attends_slaves = Attend::where("unit_id", $unit_slave->id)->get();
        foreach ($attends_slaves as $attend_slave) {
          $frequency_slave = new Frequency;
          $frequency_slave->attend_id = $attend_slave->id;
          $frequency_slave->lesson_id = $lesson_slave->id;
          $frequency_slave->value = "P";
          $frequency_slave->save();
        }
      }
    }

		$lesson->id = encrypt($lesson->id);

		if(Request::isMethod('post')) {
			return ['status'=> 1, 'lesson'=> $lesson];
		}
		else {
			return redirect("/lessons?l=" . $lesson->id)->with("success", "Uma nova aula foi criada com sucesso.");
		}

  }

  public function postSave()
  {
    //~ var_dump(Input::all());

    $lesson = Lesson::find(decrypt(Input::get("l")));

    $lesson->date = Input::get("date-year") . "-" . Input::get("date-month") . "-" . Input::get("date-day");
    $lesson->title = Input::get("title");
    $lesson->description = Input::get("description");
    $lesson->goals = Input::get("goals");
    $lesson->content = Input::get("content");
    $lesson->methodology = Input::get("methodology");
    $lesson->resources = Input::get("resources");
    $lesson->valuation = Input::get("valuation");
    $lesson->estimatedTime = Input::get("estimatedTime");
    $lesson->keyworks = Input::get("keyworks");
    $lesson->bibliography = Input::get("bibliography");
    $lesson->notes = Input::get("notes");
    $lesson->save();

    //~ return $lesson;
    $unit = DB::select("SELECT Units.id, Units.status
                          FROM Units, Lessons
                          WHERE Units.id = Lessons.unit_id AND
                            Lessons.id=?", [$lesson->id]);

    return redirect("/lectures/units?u=" . encrypt($unit[0]->id))->with("success", "Aula atualizada com sucesso");
  }

  public function anyFrequency()
  {
    $attend = Attend::find(decrypt(Input::get("attend_id")));
    $lesson_id = decrypt(Input::get("lesson_id"));
    $value = Input::get("value") == "P" ? "F" : "P";

    $offer_id = DB::select(
      "SELECT Units.offer_id "
      . "  FROM Lessons, Units "
      . "WHERE Lessons.id = ? "
      . "  AND Lessons.unit_id = Units.id",
      [$lesson_id]
    )[0]->offer_id;

    $status = Frequency::where("attend_id", $attend->id)->where("lesson_id", $lesson_id)->update(["value" => $value]);

    $frequency = DB::select(
      "SELECT Offers.maxlessons, COUNT(*) as qtd "
      . "  FROM Offers, Units, Attends, Frequencies "
      . "WHERE Offers.id = ? "
      . "  AND Offers.id = Units.offer_id "
      . "  AND Units.id = Attends.unit_id "
      . "  AND Attends.user_id = ? "
      . "  AND Attends.id = Frequencies.attend_id "
      . "  AND Frequencies.value = 'F'",
      [$offer_id, $attend->user_id]
    )[0];

  	$this->slavesFrequency($attend->id, $lesson_id, $value);

    return Response::json(["status" => $status, "value" => $value, "frequency" => sprintf("%d (%.1f %%)", $frequency->qtd, 100. * $frequency->qtd / $frequency->maxlessons)]);
  }

  /**
   * Replica a frequência para as ofertas slaves. A frequência é replicada por
   * aluno por aula em oferta. É verificado se o aluno existe nas ofertas slaves.
   *
   * @param  [type] $attend_id [description]
   * @param  [type] $lesson_id [description]
   * @param  [type] $value    [description]
   * @return [type]           [description]
   */
  private function slavesFrequency($attend_id, $lesson_id, $value)
  {
    if (Attend::find($attend_id)->getUnit()->offer->grouping != 'M') {
      return;
    }
		$unit = Attend::find($attend_id)->getUnit();
		$slaveOffers = Offer::where('offer_id', $unit->offer->id)->get();
		$groupLesson = Lesson::find($lesson_id);
		$student = Attend::find($attend_id)->getUser();


		foreach($slaveOffers as $o) {
			$o_unit = Unit::where('offer_id', $o->id)->where('value', $unit->value)->first();
			$lessons = Lesson::where("unit_id", $o_unit->id)->where('date', $groupLesson->date)->whereStatus('E')->orderBy("date", "desc")->orderBy("id", "desc")->get();

			foreach ($lessons as $key => $lesson) {
				$o_attend = Attend::where('user_id', $student->id)->where('unit_id', $o_unit->id)->first();
				Frequency::where("attend_id", $o_attend->id)->where("lesson_id", $lesson->id)->update(["value" => $value]);
			}
		}
  }

  public function postDelete()
  {
    $lesson = Lesson::find(decrypt(Input::get("input-trash")));

    $unit = DB::select("SELECT Units.id, Units.status
                          FROM Units, Lessons
                          WHERE Units.id = Lessons.unit_id AND
                            Lessons.id=?", [$lesson->id]);

    if ($unit[0]->status == 'D') {
      return redirect("/lectures/units?u=" . encrypt($unit[0]->id))->with("error", "Não foi possível deletar.<br>Unidade desabilitada.");
    }
    if ($lesson) {
      $lesson->status = "D";
      $lesson->save();
      return redirect("/lectures/units?u=" . encrypt($unit[0]->id))->with("success", "Aula excluída com sucesso!");
    } else {
      return redirect("/lectures/units?u=" . encrypt($unit[0]->id))->with("error", "Não foi possível deletar");
    }
  }

  public function getInfo()
  {
    $lesson = Lesson::find(decrypt(Input::get("lesson")));
    $lesson->date = date("d/m/Y", strtotime($lesson->date));
    return $lesson;
  }

  /**
   * Faz uma cópia de uma aula com ou sem frequecia
   *    1 - cópia para a mesma unidade sem frequencia
   *    2 - cópia para a mesma unidade com frequencia
   *    3 - cópia para uma outra unidade sem frequencia
   *
   * @return type
   */
  public function anyCopy()
  {
    $lesson = Lesson::find(decrypt(Input::get("lesson")));
    $auth = DB::select("SELECT COUNT(*) as qtd FROM Units, Lectures WHERE Units.id=? AND Units.offer_id=Lectures.offer_id AND Lectures.user_id=?",
      [$lesson->unit_id, $this->user_id])[0]->qtd;
    if (!$auth) {
      return Response::JSON(false);
    }

    $copy = $lesson->replicate();
    if (Input::get("type") == 3) {
      $unit = Unit::where("offer_id", decrypt(Input::get("offer")))->whereStatus("E")->orderBy("value", "desc")->first();
      $copy->unit_id = $unit->id;
      $copy->save();

      $attends = Attend::where("unit_id", $unit->id)->get();
      foreach ($attends as $attend) {
        $frequency = new Frequency;
        $frequency->attend_id = $attend->id;
        $frequency->lesson_id = $copy->id;
        $frequency->value = "P";
        $frequency->save();
      }
    } else {
      $copy->save();
      $frequencies = Frequency::where("lesson_id", $lesson->id)->get();
      foreach ($frequencies as $frequency) {
        $frequency = $frequency->replicate();
        $frequency->lesson_id = $copy->id;
        if (Input::get("type") == 1) {
          $frequency->value = "P";
        }

        $frequency->save();

      }
      $copy->id = encrypt($copy->id);
      $copy->date = date("d/m/Y", strtotime($copy->date));
      return $copy;
    }
  }

  /**
   * seleciona as ofertas ministradas pelo professor que está logado
   *
   * @return lista das ofertas
   */
  public function postListOffers()
  {
    $offers = DB::select("SELECT Offers.id, Disciplines.name, Classes.class, Periods.name as `periodName`, Courses.name as `courseName` FROM Lectures, Offers, Classes, Disciplines, Periods, Courses "
      . "WHERE Lectures.user_id=? AND Lectures.offer_id=Offers.id AND Offers.class_id=Classes.id AND Offers.discipline_id=Disciplines.id AND Disciplines.period_id=Periods.id AND Periods.course_id=Courses.id",
      [$this->user_id]);

    foreach ($offers as $offer) {
      $offer->id = encrypt($offer->id);
    }

		return $offers;
  }

  public function anyDelete()
  {
    Lesson::find(decrypt(Input::get("input-trash")))->delete();
    return Redirect::back()->with("alert", "Aula excluída!");
  }
}
