<?php namespace App\Http\Controllers;

class AvaliableController extends Controller
{

  /**
   * Armazena o ID do usuário
   * @var type num
   */
  private $user_id;

  public function AvaliableController()
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
    $user = User::find($this->user_id);
    $exam = Exam::find(decrypt(Input::get("e")));
    $students = null;
    if ($exam->aval == "A") {
      $students = Attend::where("unit_id", $exam->unit_id)->get();
    }
    return view("modules.avaliable", ["user" => $user, "exam" => $exam, "students" => $students, "unit" => Unit::find($exam->unit_id)]);
  }

  public function postSave()
  {
    if (Input::has("exam")) {
      $exam = Exam::find(decrypt(Input::get("exam")));
    } else {
      $exam = new Exam;
      $exam->unit_id = decrypt(Input::get("unit"));
      $exam->aval = "A";
    }
    $exam->date = Input::get("date-year") . "-" . Input::get("date-month") . "-" . Input::get("date-day");
    $exam->title = Input::get("title");

    if (Input::get("weight") != "") {
      $exam->weight = Input::get("weight");
    }
    $exam->type = Input::get("type");
    $exam->comments = Input::get("comment");
    $exam->save();

    if (!Input::has("exam")) {
      $this->createExamsValues($exam);
    }
    return redirect("/lectures/units?u=" . encrypt($exam->unit_id))->with("success", "Avaliação atualizada com sucesso.");
  }

  /**
   * Cria os ExamsValue para o Exam (avaliação) criado.
   * @param  Exam  $exam  [Objeto Exam com dados da avaliação]
   * @return [boolean]  [Retorna true em caso de sucesso ou false caso aconteça algum erro]
   */
  private function createExamsValues(Exam $exam)
  {
    try {
      $attends = Attend::where("unit_id", $exam->unit_id)->get();
      foreach ($attends as $attend) {
        $value = new ExamsValue;
        $value->attend_id = $attend->id;
        $value->exam_id = $exam->id;
        $value->value = "";
        $value->save();
      }
      return true;
    } catch (Exception $e) {
      Log::info('createExamsValues Error', ['message' => $e->getMessage()]);
      return false;
    }


  }

  public function getNew()
  {
    $user = User::find($this->user_id);
    $unit = Unit::find(decrypt(Input::get("u")));
    $exam = new Exam;
    $exam->unit_id = $unit->id;
    $exam->date = date("Y-m-d");
    $exam->title = "Sem título";
    $exam->aval = "A";
    $exam->weight = "1";
    $exam->type = 2;

    return view("modules.avaliable", ["user" => $user, "exam" => $exam, "students" => [], "unit" => $unit]);
  }

  public function postExam()
  {
    try {
      $exam = decrypt(Input::get("exam"));
      $attend = decrypt(Input::get("student"));
      $value = (float) str_replace(",", ".", Input::get("value"));

      $a = Attend::find($attend);
      $average = $a->getUnit()->getOffer()->getClass()->getPeriod()->getCourse()->average;

      if (($average > 10 && ($value > 100 || $value < 0)) || ($average <= 10 && ($value > 10 || $value < 0))) {
        throw new Exception('Invalid value.');
      } else {
        if ($average <= 10) {
          $value = sprintf("%.2f", $value);
        }
      }
      if (ExamsValue::where("attend_id", $attend)->where("exam_id", $exam)->first()) {
        ExamsValue::where("attend_id", $attend)->where("exam_id", $exam)->update(["value" => $value]);
      } else {
        $examsvalue = new ExamsValue;
        $examsvalue->attend_id = $attend;
        $examsvalue->exam_id = $exam;
        $examsvalue->value = $value;
        $examsvalue->save();
      }
      return $value;
    } catch (Exception $e) {
      return "error";
    }
  }

  public function postExamDescriptive()
  {
    try {
      $exam = decrypt(Input::get("exam"));
      $attend = decrypt(Input::get("student"));
      $examsvalue = DescriptiveExam::where("attend_id", $attend)->where("exam_id", $exam)->first();
      if ($examsvalue) {
        DescriptiveExam::where("attend_id", $attend)->where("exam_id", $exam)->update(["description" => Input::get("description"), "approved" => Input::get("approved")]);
      } else {
        $examsvalue = new DescriptiveExam;
        $examsvalue->attend_id = $attend;
        $examsvalue->exam_id = $exam;
        $examsvalue->description = Input::get("description");
        $examsvalue->approved = Input::get("approved");
        $examsvalue->save();
      }
      return Response::json([
        "status" => 1,
        "description" => $examsvalue->description,
        "approved" => $examsvalue->approved,
      ]);
    } catch (Exception $e) {
      return Response::json(["status" => 0, "message" => $e->getMessage()]);
    }
  }

  public function getFinalunit($unit = "")
  {
    try
    {
      $user = User::find($this->user_id);
      $unit = decrypt($unit);
      $final = Exam::whereAval("R")->where("unit_id", $unit)->first();
      if (!$final) {
        $final = new Exam;
        $final->aval = "R";
        $final->title = "Recuperação da Unidade";
        $final->type = 2;
        $final->unit_id = $unit;
        $final->date = date("Y-m-d");
      }
      $course = Unit::find($unit)->getOffer()->getDiscipline()->getPeriod()->getCourse();
      $attends = Attend::where("unit_id", $unit)->get();
      return view("modules.units.retrieval", ["exam" => $final, "user" => $user, "attends" => $attends, "average" => $course->average]);
    } catch (Exception $e) {
      return "$e";
    }
  }

  public function postFinalunit($unit = "")
  {
    $cUnit = Unit::find(decrypt($unit));
    $exam = Exam::where("unit_id", $cUnit->id)->whereAval("R")->first();
    if (!$exam) {
      $exam = new Exam;
      $exam->aval = "R";
      $exam->unit_id = $cUnit->id;
    }
    $exam->title = "Recuperação da Unidade $cUnit->value";
    $exam->date = Input::get("date-year") . "-" . Input::get("date-month") . "-" . Input::get("date-day");
    $exam->type = Input::get("type");
    $exam->comments = Input::get("comment");
    $exam->save();
    return redirect("/lectures/units?u=$unit")->with("success", "Avaliação atualizada com sucesso.");
//  return redirect("avaliable/finalunit/$unit")->with("message", "Avaliação atualizada com sucesso.");
  }

  public function postFinaldiscipline($id = "")
  {
    $offer = Offer::find(decrypt($id));
    $offer->dateFinal = Input::get("date-year") . "-" . Input::get("date-month") . "-" . Input::get("date-day");
    $offer->typeFinal = Input::get("type");
    $offer->comments = Input::get("comment");
    $offer->save();
    return redirect("avaliable/finaldiscipline/$id")->with("success", "Recuperação Final atualizada com sucesso");
  }

  public function postOffer()
  {
    try {
      $offer = decrypt(Input::get("offer"));
      $student = decrypt(Input::get("student"));
      $value = (float) str_replace(",", ".", Input::get("value"));

      $average = Offer::find($offer)->getClass()->getPeriod()->getCourse()->average;

      if (($average > 10 && ($value > 100 || $value < 0)) || ($average <= 10 && ($value > 10 || $value < 0))) {
        throw new Exception('Invalid value.');
      } else {
        if ($average <= 10) {
          $value = sprintf("%.2f", $value);
        }
      }

      if (FinalExam::where("user_id", $student)->where("offer_id", $offer)->first()) {
        FinalExam::where("user_id", $student)->where("offer_id", $offer)->update(["value" => $value]);
      } else {
        $offervalue = new FinalExam;
        $offervalue->user_id = $student;
        $offervalue->offer_id = $offer;
        $offervalue->value = $value;
        $offervalue->save();
      }
      return $value;
    } catch (Exception $e) {
      return "error";
    }
  }

  public function getFinaldiscipline($offer = "")
  {
    $user = User::find($this->user_id);
    $offer = Offer::find(decrypt($offer));

    /* caso não tenha data marcada, coloque a data de hoje */
    if (strtotime($offer->dateFinal) < 0) {
      $offer->dateFinal = date("Y-m-d");
    }

    if (!Lecture::where("user_id", $user->id)->where("offer_id", $offer->id)->first()) {
      return redirect("/logout");
    }
    $units = Unit::where("offer_id", $offer->id)->get();
    $course = Offer::find($offer->id)->getDiscipline()->getPeriod()->getCourse();
    $alunos = DB::select("select Users.id, Users.name
                          from Attends, Units, Users
                          where Units.offer_id=? AND Units.id=Attends.unit_id AND Attends.user_id=Users.id
													AND Attends.status = 'M'
                          group by Attends.user_id
                          order by Users.name", [$offer->id]);
    foreach ($alunos as $aluno) {
      $aluno->absence = $offer->qtdAbsences($aluno->id);
      $aluno->averages = [];
      $sum = 0.;
      foreach ($units as $unit) {
        $exam = $unit->getAverage($aluno->id);
        $aluno->averages[$unit->value] = $exam[0] < $course->average ? $exam[1] : $exam[0];
        $sum += $aluno->averages[$unit->value];
      }
      $aluno->med = $sum / count($units);
      $final = FinalExam::where("user_id", $aluno->id)->where("offer_id", $offer->id)->first();
      $aluno->final = $final ? $final->value : "";
    }
    return view("modules.disciplines.retrieval", ["user" => $user, "alunos" => $alunos, "course" => $course, "offer" => $offer]);
  }

  public function getAverageUnit($unit)
  {
    $final = Exam::whereAval("R")->where("unit_id", $unit->id)->first();
    $qtdExam = Exam::whereAval("A")->where("unit_id", $unit->id)->count();
    $sumWeight = Exam::whereAval("A")->where("unit_id", $unit->id)->sum("weight");
    $sumWeight = $sumWeight ? $sumWeight : 1;
    $attends = Attend::where("unit_id", $unit->id)->get();
    foreach ($attends as $attend) {
      if ($final and ($examfinal = ExamsValue::where("attend_id", $attend->id)->where("exam_id", $final->id)->first())) {
        $attend->final = $examfinal->value;
      } else {
        $attend->final = "F";
      }
      $values = ExamsValue::where("attend_id", $attend->id)->get();
      $sum = 0.;
      foreach ($values as $value) {
        $sum += $value->value * Exam::find($value->exam_id)->weight;
      }
      $attend->media = $sum / $sumWeight;
      $attend->name = User::find($attend->user_id)->name;
    }
    return $attends;
  }

  public function getListstudentsexam($exam = "")
  {
    $user = User::find($this->user_id);
    $exam = Exam::find(decrypt($exam));
    $students = null;

    $calculation = $exam->unit->calculation;

    if ($exam->aval == "A") {
      $students = Attend::where("unit_id", $exam->unit_id)->where("status", "M")->get();

			$students = $students->sortBy(function($student) {
				return $this->removeAccents($student->getUser()->name);
			});
    }

    switch ($calculation) {
      case "S": // Soma
      case "A": // Média Aritmética
      case "W": // Média Ponderada
        return view("modules.liststudentsexam", ["user" => $user, "exam" => $exam, "students" => $students]);
      case "P": // Parecer Descritivo
        return view("modules.liststudentsexamDescriptive", ["user" => $user, "exam" => $exam, "students" => $students]);
    }
  }

  public function postDelete()
  {
    $exam = Exam::find(decrypt(Input::get("input-trash")));

    $unit = DB::select("SELECT Units.id, Units.status
                          FROM Units, Exams
                          WHERE Units.id = Exams.unit_id AND
                            Exams.id=?", [$exam->id]);

    if ($unit[0]->status == 'D') {
      return redirect("/lectures/units?u=" . encrypt($unit[0]->id))->with("error", "Não foi possível deletar.<br>Unidade desabilitada.");
    }
    if ($exam) {
      $exam->status = "D";
      $exam->save();
      return redirect("/lectures/units?u=" . encrypt($unit[0]->id))->with("success", "Avaliação excluída com sucesso!");
    } else {
      return redirect("/lectures/units?u=" . encrypt($unit[0]->id))->with("error", "Não foi possível deletar");
    }
  }

	function removeAccents($str) {
	  $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', 'Ά', 'ά', 'Έ', 'έ', 'Ό', 'ό', 'Ώ', 'ώ', 'Ί', 'ί', 'ϊ', 'ΐ', 'Ύ', 'ύ', 'ϋ', 'ΰ', 'Ή', 'ή');

		$b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', 'Α', 'α', 'Ε', 'ε', 'Ο', 'ο', 'Ω', 'ω', 'Ι', 'ι', 'ι', 'ι', 'Υ', 'υ', 'υ', 'υ', 'Η', 'η');
	  return str_replace($a, $b, $str);
	}
}
