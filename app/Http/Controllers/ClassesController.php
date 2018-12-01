<?php namespace App\Http\Controllers;

class ClassesController extends Controller
{
  private $user_id;

  public function ClassesController()
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
      $courses = Course::where("institution_id", $this->user_id)->where("status", "E")->orderBy("name")->get();
      $listPeriod = [];
			$listPeriodLetivo = [];
			$year = Input::has('year') ? (int) Input::get('year') : (int) date('Y');
      foreach ($courses as $course) {
        $periods = Period::where("course_id", $course->id)->orderBy("name")->get();
        //~ $listPeriod[$course->name] = [];
        foreach ($periods as $period) {
          $listPeriod[$course->name][encrypt($period->id)] = $period->name;
        }
      }

      $classes = DB::select("SELECT Classes.id AS id, Periods.name AS period,
															Classes.name AS classe_name, Classes.schoolYear AS schoolYear, Classes.class AS classe,
															Courses.name AS name, Classes.status AS status
														 FROM Courses, Periods, Classes
														 WHERE Courses.institution_id=? AND
															Courses.status = 'E' AND
															Classes.status <> 'D' AND
															Classes.schoolYear IN ($year, $year-1) AND
															Periods.course_id=Courses.id AND
															Classes.period_id=Periods.id", [$user->id]);
      //~ return $classes;
			$atual_classes = array_where($classes, function($key, $classe) use($year)
			{
				return $classe->schoolYear == $year;
			});

			$previous_classes = array_where($classes, function($key, $classe) use($year)
			{
				return $classe->schoolYear == $year-1;
			});

      return view("modules.classes", ["listPeriod" => $listPeriod, "user" => $user, "classes" => $classes,  "atual_classes" => $atual_classes,  "previous_classes" => $previous_classes, "schoolYear" => $year]);
    } else {
      return redirect("/");
    }
  }

	public function postClassesByYear()
	{
		if ($this->user_id) {
			$user = User::find($this->user_id);
			$year = Input::has('year') ? (int) Input::get('year') : (int) date('Y');
			// $year_previous = $year - 1;

			$classes = DB::select(
				"SELECT Classes.id AS id, Periods.name AS period,
				CONCAT('[', Classes.class, '] ', Classes.name) AS classe, Classes.status AS status
				FROM Courses, Periods, Classes
				WHERE Courses.institution_id=? AND
				Courses.status = 'E' AND
				Classes.status <> 'D' AND
				Classes.schoolYear=$year AND
				Periods.course_id=Courses.id AND
				Classes.period_id=Periods.id", [$user->id]
			);

			return ['classes' => $classes];
		}
		else {
			return redirect("/");
		}
	}

  public function getPanel()
  {
    if ($this->user_id) {
      $user = User::find($this->user_id);
      $courses = Course::where("institution_id", $this->user_id)->where("status", "E")->orderBy("name")->get();
      $listCourses = [];
      foreach ($courses as $course) {
        $listCourses[encrypt($course->id)] = $course->name;
      }
      return view("modules.panel", ["listCourses" => $listCourses, "user" => $user]);
    } else {
      return redirect("/");
    }
  }

  public function postListdisciplines()
  {
    if (Input::has("flag")) {
      $offers = Offer::where("class_id", decrypt(Input::get("classe_id")))->get();
      $registered_disciplines_ids = [];
      foreach ($offers as $offer) {
        $registered_disciplines_ids[] = $offer->discipline_id;
      }
      $disciplines = Discipline::where("period_id", decrypt(Input::get("period_id")))->whereStatus('E')->whereNotIn('id', $registered_disciplines_ids)->get();
    } else {
      $disciplines = Discipline::where("period_id", decrypt(Input::get("period")))->whereStatus('E')->get();
    }
    return view("modules.disciplines.listOffer", ["disciplines" => $disciplines]);
  }

  public function postNew()
  {
    $class = new Classe;
    $class->period_id = decrypt(Input::get("period"));
    $class->name = Input::get("name");
    $class->class = Input::get("class");
    $class->status = 'E';
    $class->save();
    foreach (Input::all() as $key => $value) {
      if (strstr($key, "discipline_") != false) {
        $offer = new Offer;
        $offer->class_id = $class->id;
        $offer->discipline_id = decrypt($value);
        $offer->save();

        $unit = new Unit;
        $unit->offer_id = $offer->id;
        $unit->value = "1";
        $unit->calculation = "A";
        $unit->save();
      }
    }
    return redirect("classes")->with("success", "Turma criada com sucesso!");
  }

  public function getInfo()
  {
    $class = Classe::find(decrypt(Input::get("classe")));
    $class->period_idCrypt = encrypt($class->period_id);
    $class->course = Course::find(Period::find($class->period_id)->course_id);

    return $class;
  }

  public function postEdit()
  {
    $class = Classe::find(decrypt(Input::get("classId")));
    if ($class) {
      $class->name = Input::get("class");
      $class->save();
      foreach (Input::all() as $key => $value) {
        if (strstr($key, "discipline_") != false) {
          $offer = new Offer;
          $offer->class_id = $class->id;
          $offer->discipline_id = decrypt($value);
          $offer->save();

          $unit = new Unit;
          $unit->offer_id = $offer->id;
          $unit->value = "1";
          $unit->calculation = "A";
          $unit->save();
        }
      }
      return redirect("/classes")->with("success", "Classe editada com sucesso!");
    }
    return redirect("/classes")->with("error", "Não foi possível editar!");

  }

  public function postDelete()
  {
    $class = Classe::find(decrypt(Input::get("input-trash")));
    if ($class) {
      $class->status = "D";
      $class->save();
      return redirect("/classes")->with("success", "Excluído com sucesso!");
    } else {
      return redirect("/classes")->with("error", "Não foi possível excluir!");
    }
  }

  public function postChangeStatus()
  {
    $id = decrypt(Input::get("key"));

    $class = Classe::find($id);
    if ($class) {
      $class->status = Input::get("status");
      $class->save();
      if ($class->status == "E") {
        return Redirect::back()->with("success", "Turma ativada com sucesso!");
      }
			else if ($class->status == "F") {
        return Redirect::back()->with("success", "Turma encerrada com sucesso!");
			} else {
        return Redirect::back()->with("success", "Turma bloqueada com sucesso!<br/>Turmas bloqueadas são movidas para o final.");
      }

    } else {
      return Redirect::back()->with("error", "Não foi possível realizar essa operação!");
    }

  }

  public function anyListOffers()
  {
    $offers = Offer::where("class_id", decrypt(Input::get("class")))->get();
    $idStudent = decrypt(Input::get("student"));

    foreach ($offers as $offer) {
      $offer->status = DB::select("SELECT count(*) as qtd FROM Units, Attends " .
        "WHERE Units.offer_id=? AND Units.id=Attends.unit_id AND Attends.user_id=?",
        [$offer->id, $idStudent])[0]->qtd;

      $offer->name = Discipline::find($offer->discipline_id)->name;
      $offer->id = encrypt($offer->id);
    }

    return $offers;
  }

  /**
   * Faz uma busca por todos os cursos da instituição e suas unidades ativas
   *
   *
   * @return json com cursos e unidades
   */
  public function postListUnits($status = 1)
  {
    $status = ((int) $status ? "E" : "D");

    $courses = Course::where("institution_id", $this->user_id)->whereStatus("E")->get();
    foreach ($courses as $course) {
      $course->units = DB::select("SELECT Units.value
																		 FROM Periods, Classes, Offers, Units
																		WHERE Periods.course_id=?
																					AND Periods.id=Classes.period_id
																					AND Classes.id=Offers.class_id
																					AND Classes.status='E'
																					AND Offers.id=Units.offer_id
																					AND Units.status=?
																 GROUP BY Units.value", [$course->id, $status]);

      $course->id = encrypt($course->id);
    }

    return $courses;
  }

  public function postBlockUnit()
  {
    $course = Course::find(decrypt(Input::get("course")));
    if ($course->institution_id != $this->user_id) {
      throw new Exception('Usuário inválido');
    }

    $periods = Period::where("course_id", $course->id)->get();
    foreach ($periods as $period) {
      $classes = Classe::where("period_id", $period->id)->get();
      foreach ($classes as $class) {
        $offers = Offer::where("class_id", $class->id)->get();
        foreach ($offers as $offer) {
          Unit::where("offer_id", $offer->id)->whereValue(Input::get("unit"))->whereStatus("E")->update(array('status' => "D"));
        }

      }
    }
  }

  public function postUnblockUnit()
  {
    $course = Course::find(decrypt(Input::get("course")));
    if ($course->institution_id != $this->user_id) {
      throw new Exception('Usuário inválido');
    }

    $periods = Period::where("course_id", $course->id)->get();
    foreach ($periods as $period) {
      $classes = Classe::where("period_id", $period->id)->get();
      foreach ($classes as $class) {
        $offers = Offer::where("class_id", $class->id)->get();
        foreach ($offers as $offer) {
          Unit::where("offer_id", $offer->id)->whereValue(Input::get("unit"))->whereStatus("D")->update(array('status' => "E"));
        }

      }
    }
  }

  public function anyCreateUnits()
  {
    $s_attends = false;
    $course = Course::find(decrypt(Input::get("course")));
    if ($course->institution_id != $this->user_id) {
      throw new Exception("Você não tem permissão para realizar essa operação");
    }

    $offers = DB::select("SELECT Offers.id FROM Periods, Classes, Offers "
      . "WHERE Periods.course_id=? AND Periods.id=Classes.period_id AND Classes.id=Offers.class_id", [$course->id]);

    if (!count($offers)) {
      throw new Exception("Não possui ofertas nesse curso.");
    }

    foreach ($offers as $offer) {
      $old = Unit::where("offer_id", $offer->id)->orderBy("value", "desc")->first();

      $unit = new Unit;
      $unit->offer_id = $old->offer_id;
      $unit->value = $old->value + 1;
      $unit->calculation = $old->calculation;
      $unit->save();

      $attends = Attend::where("unit_id", $old->id)->get();

      $s_attends = false;
      foreach ($attends as $attend) {
        if (!$s_attends) {
          $s_attends = "INSERT IGNORE INTO Attends (unit_id, user_id) VALUES ($unit->id, $attend->user_id)";
        } else {
          $s_attends .= ", ($unit->id, $attend->user_id)";
        }

        //  $new = new Attend;
        //  $new->unit_id = $unit->id;
        //  $new->user_id = $attend->user_id;
        //  $new->save();
      }
      if ($s_attends) {
        DB::insert($s_attends);
      }

    }
  }

	public function postCopyToYear()
  {
		if(Input::has('classes')) {
			foreach(Input::get('classes') as $in) {
				$classe = Classe::find($in['classe_id']);

				$new_classe = new Classe();
				$new_classe->period_id = $classe->period_id;
				$new_classe->name = $classe->name;
				$new_classe->schoolYear = $classe->schoolYear + 1;
				$new_classe->class = '';
				$new_classe->status = 'E';

				$new_classe->save();

				if($in['with_offers'] == "false") {
					continue;
				}

				$offers = Offer::where('class_id', $classe->id)->get();
				$tmp_groups = [];
				foreach($offers as $offer) {
					if($offer->grouping != "M") { // Diferente de master, porque a master é criada quando há slaves.
						if($offer->grouping == 'S')  { // Criar grupo de ofertas / Oferta Slave
							if(isset($tmp_groups[$offer->offer_id])) { // Se o grupo já foi criado
								$this->createOffer($offer, $new_classe, $tmp_groups[$offer->offer_id]); //Cria oferta apontado para o novo grupo existente.
							}
							else {
								$group = Offer::find($offer->offer_id);
								$tmp_groups[$offer->offer_id] = $this->createOffer($group, $new_classe, null); //Cria grupo de oferta e guarda o id;
								$this->createOffer($offer, $new_classe, $tmp_groups[$offer->offer_id]);
							}
						}
						else {
							$this->createOffer($offer, $new_classe, null); //Duplica oferta para o novo ano letivo
						}
					}

					// echo("$new_classe->name | $offer->id | ");
				}

			}

			return ['status' => 1, 'tmp' => $tmp_groups];
		} else {
			return ['status' => 0, 'message' => 'Não foi possível copiar as classes'];
		}
  }

	public function createOffer($offer, $classe, $group) {

		$new_offer = new Offer();
		$new_offer->class_id = $classe->id;
		$new_offer->discipline_id = $offer->discipline_id;
		$new_offer->classroom = $offer->classroom;
		$new_offer->day_period = $offer->day_period;
		$new_offer->maxlessons = $offer->maxlessons;
		$new_offer->typeFinal = '';
		$new_offer->dateFinal = '';
		$new_offer->comments = $offer->comments;
		$new_offer->status = 'E';
		$new_offer->grouping = $offer->grouping;

		if($offer->grouping == "S") {
			$new_offer->offer_id = $group;
		}

		$new_offer->save();
		//Cria uma unidade
		$unit = new Unit();
		$unit->offer_id = $new_offer->id;
		$unit->value = 1;
		$unit->save();

		return $new_offer->id;

	}
}
