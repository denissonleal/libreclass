<?php namespace App\Http\Controllers;

class UsersController extends Controller
{

  private $user_id;

  public function UsersController()
  {
    $id = session("user");
    if ($id == null || $id == "") {
      $this->user_id = false;
    } else {
      $this->user_id = decrypt($id);
    }
  }

  public function postSearchTeacher()
  {
    $teacher = User::where('email', request()->get('str'))->first();
    \Log::info('post search teacher', [$teacher]);
    if ($teacher) {
      $relationship = Relationship::where('user_id', $this->user_id)->where('friend_id', $teacher->id)->first();
      if (!$relationship) {
        return Response::json([
          'status' => 1,
          'teacher' => [
            'id' => encrypt($teacher->id),
            'name' => $teacher->name,
            'formation' => $teacher->formation,
          ],
          'message' => 'Este professor já está cadastrado no LibreClass e será vinculado à sua instituição.',
        ]);
      } else {
        return Response::json([
          'status' => -1,
          'teacher' => [
            'id' => encrypt($teacher->id),
            'name' => $teacher->name,
            'formation' => $teacher->formation,
            'enrollment' => $relationship->enrollment,
          ],
          'message' => 'Este professor já está vinculado à instituição!',
        ]);
      }
    } else {
      return Response::json([
        'status' => 0,
      ]);
    }
  }

  public function anyTeachersFriends()
  {
    $teachers = DB::select("SELECT Users.id, Users.name, Users.photo, Relationships.enrollment as 'comment'"
      . "FROM Users, Relationships "
      . "WHERE Relationships.user_id=? AND Relationships.type='2' "
      . "AND Relationships.friend_id=Users.id "
      . "AND Relationships.status='E'"
      . " ORDER BY name",
      [$this->user_id]);
    foreach ($teachers as $teacher) {
      $teacher->id = base64_encode($teacher->id);
    }

    return $teachers;
  }

  public function getTeacher()
  {
    if ($this->user_id) {
      $block = 30;
      $search = request()->has("search") ? request()->get("search") : "";
      $current = (int) request()->has("current") ? request()->get("current") : 0;
      $user = User::find($this->user_id);
      $courses = Course::where("institution_id", $this->user_id)
        ->whereStatus("E")
        ->orderBy("name")
        ->get();
      $listCourses = ["" => ""];
      foreach ($courses as $course) {
        $listCourses[$course->name] = $course->name;
      }

      $relationships = DB::select("SELECT Users.id, Users.name, Relationships.enrollment, Users.type "
        . "FROM Users, Relationships "
        . "WHERE Relationships.user_id=? AND Relationships.type='2' AND Relationships.friend_id=Users.id "
        . "AND Relationships.status='E' AND (Users.name LIKE ? OR Relationships.enrollment=?) "
        . " ORDER BY name LIMIT ? OFFSET ?",
        [$this->user_id, "%$search%", $search, $block, $current * $block]);

      $length = DB::select("SELECT count(*) as 'length' "
        . "FROM Users, Relationships "
        . "WHERE Relationships.user_id=? AND Relationships.type='2' AND Relationships.friend_id=Users.id "
        . "AND (Users.name LIKE ? OR Relationships.enrollment=?) ", [$this->user_id, "%$search%", $search]);

      return view(
        "modules.addTeachers",
        [
          "courses" => $listCourses,
          "user" => $user,
          "relationships" => $relationships,
          "length" => (int) $length[0]->length,
          "block" => (int) $block,
          "current" => (int) $current,
        ]
      );

    } else {
      return redirect("/");
    }
  }

  public function postTeacher()
  {
    // Verifica se o número de matrícula já existe

    if (strlen(request()->get("teacher"))) {
      $user = User::find(decrypt(request()->get("teacher")));
      if (strlen(request()->get("registered"))) {
        $relationship = Relationship::where('user_id', $this->user_id)->where('friend_id', $user->id)->first();
        if (!$relationship) {
          $relationship = new Relationship;
          $relationship->user_id = $this->user_id;
          $relationship->friend_id = $user->id;
          $relationship->enrollment = request()->get('enrollment');
          $relationship->status = "E";
          $relationship->type = "2";
          $relationship->save();
        }
        return redirect("/user/teacher")->with("success", "Professor vinculado com sucesso!");
      }

      // Tipo P é professor com conta liberada. Ele mesmo deve atualizar as suas informações e não a instituição.
      if ($user->type == "P") {
        return redirect("/user/teacher")->with("error", "Professor não pode ser editado!");
      }
      $user->email = request()->get("email");
      // $user->enrollment = request()->get("enrollment");
      $user->name = request()->get("name");
      $user->formation = request()->get("formation");
      $user->gender = request()->get("gender");
      $user->save();
      return redirect("/user/teacher")->with("success", "Professor editado com sucesso!");
    } else {
      $verify = Relationship::whereEnrollment(request()->get("enrollment"))->where('user_id', $this->user_id)->first();
      if (isset($verify) || $verify != null) {
        return redirect("/user/teacher")->with("error", "Este número de inscrição já está cadastrado!");
      }
      $user = new User;
      $user->type = "M";
      // $user->email = request()->get("email");
      // $user->enrollment = request()->get("enrollment");
      $user->name = request()->get("name");
      $user->formation = request()->get("formation");
      $user->gender = request()->get("gender");
      if (request()->has("date-year")) {
        $user->birthdate = request()->get("date-year") . "-"
        . request()->get("date-month") . "-"
        . request()->get("date-day");
      }
      $user->save();

      $relationship = new Relationship;
      $relationship->user_id = $this->user_id;
      $relationship->friend_id = $user->id;
      $relationship->enrollment = request()->get("enrollment");
      $relationship->status = "E";
      $relationship->type = "2";
      $relationship->save();

      $this->postInvite($user->id);

      return redirect("/user/teacher")->with("success", "Professor cadastrado com sucesso!");
    }
  }

  public function updateEnrollment()
  {
    $user = User::find(decrypt(request()->get("teacher")));
    Relationship::where('user_id', $this->user_id)->where('friend_id', $user->id)->update(['enrollment' => request()->get('enrollment')]);
    return redirect("/user/teacher")->with("success", "Matrícula editada com sucesso!");
  }

  public function getProfileStudent()
  {
    $user = User::find($this->user_id);
    $profile = decrypt(request()->get("u"));
    $classes = DB::select("SELECT Classes.id, Classes.name, Classes.class FROM Classes, Periods, Courses "
      . "WHERE Courses.institution_id=? AND Courses.id=Periods.course_id AND Periods.id=Classes.period_id AND Classes.status='E'",
      [$user->id]);
    $listclasses = [];
    $listidsclasses = [];
    foreach ($classes as $class) {
      $listclasses[$class->class] = $class->class;
      $listidsclasses[encrypt($class->id)] = "[$class->class] $class->name";

    }

    if ($profile) {
      $profile = User::find($profile);
			$courses = DB::select("SELECT Courses.id, Courses.name, Courses.quantUnit FROM Attends, Units, Offers, Disciplines, Periods, Courses, Classes "
			. " WHERE Units.id = Attends.unit_id "
			. " AND Offers.id = Units.offer_id "
			. " AND Disciplines.id = Offers.discipline_id "
			. " AND Periods.id = Disciplines.period_id "
			. " AND Courses.id = Periods.course_id "
			. " AND Attends.user_id = ? "
			. " GROUP BY Courses.id", [$profile->id]);

			$listCourses = [];
			foreach ($courses as $course) {
	      $listCourses[encrypt($course->id)] = "$course->name";

	    }

      $attests = Attest::where("idStudent", $profile->id)->where("institution_id", $user->id)->orderBy("date", "desc")->get();
      return view("modules.profilestudent", ["user" => $user, "profile" => $profile, "listclasses" => $listclasses, "attests" => $attests, "listidsclasses" => $listidsclasses, "listCourses" => $listCourses, 'courses' => $courses]);
    } else {
      return redirect("/");
    }
  }

	public function postGetStudent() {
		$student = User::whereId(decrypt(request()->get('student_id')))->first(['id', 'name', 'email', 'birthdate', 'enrollment', 'gender', 'course']);
		if(!$student) {
			return ['status'=>0, 'message'=> 'Não encontrado'];
		}
		$student->id = encrypt($student->id);
		return ['status' => 1, 'student'=> $student];
	}

  public function anyReporterStudentClass()
  {
    $student = decrypt(request()->get("student"));
    $disciplines = DB::select("SELECT  Courses.id as course, Disciplines.name, Offers.id as offer, Attends.id as attend, Classes.status as statusclasse "
      . "FROM Classes, Periods, Courses, Disciplines, Offers, Units, Attends "
      . "WHERE Courses.institution_id=? AND Courses.id=Periods.course_id AND Periods.id=Classes.period_id AND Classes.schoolYear=? AND Classes.id=Offers.class_id AND Offers.discipline_id=Disciplines.id AND Offers.id=Units.offer_id AND Units.id=Attends.unit_id AND Attends.user_id=? "
      . "group by Offers.id",
      [$this->user_id, request()->get("class"), $student]);

    foreach ($disciplines as $discipline) {
      $sum = 0;
      $discipline->units = Unit::where("offer_id", $discipline->offer)->get();
      foreach ($discipline->units as $unit) {
        $unit->exams = Exam::where("unit_id", $unit->id)->orderBy("aval")->get();
        foreach ($unit->exams as $exam) {
          $exam->value = ExamsValue::where("exam_id", $exam->id)->where("attend_id", $discipline->attend)->first();
        }

        $value = $unit->getAverage($student);
        // return $value;
        $sum += isset($value[1]) ? $value[1] : $value[0];
      }
      $discipline->average = sprintf("%.2f", ($sum + .0) / count($discipline->units));
      $discipline->final = FinalExam::where("user_id", $student)->where("offer_id", $discipline->offer)->first();
      $offer = Offer::find($discipline->offer);
      $discipline->absencese = sprintf("%.1f", (100. * ($offer->maxlessons - $offer->qtdAbsences($student))) / $offer->maxlessons);

      $course = Course::find($discipline->course);
      $discipline->course = $course;
      $discipline->aproved = "-";
      if ($discipline->statusclasse == "C") {
        $discipline->aproved = "Aprovado";
        if ($discipline->absencese + $course->absent_percent < 100) {
          $discipline->aproved = "Reprovado";
        }

        if ($discipline->average < $course->average and (!$discipline->final or $discipline->final->value < $course->average_final)) {
          $discipline->aproved = "Reprovado";
        }

      }
    }
    return view("institution.reportStudentDetail", ["disciplines" => $disciplines]);
  }

  public function getReporterStudentOffer()
  {
    return request()->all();
  }

  public function postProfileStudent()
  {
    try {
      $user_id = (int) decrypt(request()->get("student"));

      foreach (request()->get("offers") as $offer) {
        $units = Unit::where("offer_id", decrypt($offer))->get();
        foreach ($units as $unit) {
          $attend = Attend::where("user_id", $user_id)->where("unit_id", $unit->id)->first();
          if ($attend) {
            $disc = Offer::find(decrypt($offer))->getDiscipline();
            return Redirect::back()
              ->with("error", "O aluno não pode ser inserido.<br>"
                . "O aluno já está matriculado na oferta da disciplina <b>" . $disc->name . "</b>."); //. " com o status " . $attend->status . ".");
          }
        }
      }

      foreach (request()->get("offers") as $offer) {
        $units = Unit::where("offer_id", decrypt($offer))->get();
        foreach ($units as $unit) {
          $attend = new Attend;
          $attend->user_id = $user_id;
          $attend->unit_id = $unit->id;
          $attend->save();
          $exams = Exam::where("unit_id", $unit->id)->get();
          foreach ($exams as $exam) {
            $value = new ExamsValue;
            $value->exam_id = $exam->id;
            $value->attend_id = $attend->id;
            $value->save();
          }
          $lessons = Lesson::where("unit_id", $unit->id)->get();
          foreach ($lessons as $lesson) {
            $value = new Frequency;
            $value->lesson_id = $lesson->id;
            $value->attend_id = $attend->id;
            $value->save();
          }
        }
      }
      return Redirect::back()->with("success", "Inserido com sucesso!");
    } catch (Exception $ex) {
      return Redirect::back()->with("error", "Ocorreu algum erro inesperado.<br>Informe o suporte.");
    }
  }

  /**
   * Cadastra um atestada e retorna para a página anterior
   */
  public function postAttest()
  {
    $idStudent = decrypt(request()->get("student"));
    $relation = Relationship::where("user_id", $this->user_id)->where("friend_id", $idStudent)->whereType(1)->whereStatus("E")->first();

    if ($relation) {
      $attest = new Attest;
      $attest->institution_id = $this->user_id;
      $attest->idStudent = $idStudent;
      $attest->date = request()->get("date-year") . "-" . request()->get("date-month") . "-" . request()->get("date-day");
      $attest->days = request()->get("days");
      $attest->description = request()->get("description");
      $attest->save();

      return Redirect::back()->with("success", "Operação realizada com sucesso.");
    } else {
      return Redirect::back()->with("error", "Essa operação não pode ser realizado. Consulte o suporte.");
    }

  }

  public function getProfileTeacher()
  {
    $user = User::find($this->user_id);
    $profile = decrypt(request()->get("u"));
    if ($profile) {
      $profile = User::find($profile);
      $relationship = Relationship::where('user_id', $this->user_id)->where('friend_id', $profile->id)->first();
      $profile->enrollment = $relationship->enrollment;
      switch ($profile->formation) {
        case '0':$profile->formation = "Não quero informar";
          break;
        case '1':$profile->formation = "Ensino Fundamental";
          break;
        case '2':$profile->formation = "Ensino Médio";
          break;
        case '3':$profile->formation = "Ensino Superior Incompleto";
          break;
        case '4':$profile->formation = "Ensino Superior Completo";
          break;
        case '5':$profile->formation = "Pós-Graduado";
          break;
        case '6':$profile->formation = "Mestre";
          break;
        case '7':$profile->formation = "Doutor";
          break;
      }
      return view("modules.profileteacher", ["user" => $user, "profile" => $profile]);
    } else {
      return redirect("/");
    }
  }

  public function postInvite($id = null)
  {
    $user = User::find($this->user_id);
    if ($id) {
      $guest = User::find($id);
    } else {
      $guest = User::find(decrypt(request()->has("teacher") ? request()->get("teacher") : request()->get("guest")));
    }

    if (($guest->type == "M" or $guest->type == "N") and Relationship::where("user_id", $this->user_id)->where("friend_id", $guest->id)->first()) {
      if (User::whereEmail(request()->get("email"))->first()) {
        return Redirect::back()->with("error", "O email " . request()->get("email") . " já está cadastrado.");
      }
      try
      {
        $guest->email = request()->get("email");
        $password = substr(md5(microtime()), 1, rand(4, 7));
        $guest->password = Hash::make($password);
        Mail::send('email.invite', [
          "institution" => $user->name,
          "name" => $guest->name,
          "email" => $guest->email,
          "password" => $password,
        ], function ($message) use ($guest) {
          $message->to(request()->get("email"), $guest->name)
            ->subject("Seja bem-vindo");
        });
        $guest->save();
        return Redirect::back()->with("success", "Operação realizada com sucesso. Os dados de acesso de $guest->name foi enviado para o email $guest->email.");
      } catch (Exception $e) {
        return Redirect::back()->with("error", "Erro ao realizar a operação, tente mais tarde (" . $e->getMessage() . ")");
      }
    } else {
      return Redirect::back()->with("error", "Operação inválida");
    }
  }

  public function getStudent()
  {
    if ($this->user_id) {
      $block = 30;
      $search = request()->has("search") ? request()->get("search") : "";
      $current = (int) request()->has("current") ? request()->get("current") : 0;
      $user = User::find($this->user_id);
      $courses = Course::where("institution_id", $this->user_id)
        ->whereStatus("E")
        ->orderBy("name")
        ->get();

      $listCourses = ["" => ""];
      foreach ($courses as $course) {
        $listCourses[$course->id] = $course->name;
      }

      $relationships = DB::select("SELECT Users.id, Users.name, Users.enrollment "
        . "FROM Users, Relationships "
        . "WHERE Relationships.user_id=? AND Relationships.type='1' AND Relationships.friend_id=Users.id "
        . "AND (Users.name LIKE ? OR Users.enrollment=?) "
        . " ORDER BY name LIMIT ? OFFSET ?",
        [$this->user_id, "%$search%", $search, $block, $current * $block]);

      $length = DB::select("SELECT count(*) as 'length' "
        . "FROM Users, Relationships "
        . "WHERE Relationships.user_id=? AND Relationships.type='1' AND Relationships.friend_id=Users.id "
        . "AND (Users.name LIKE ? OR Users.enrollment=?) ", [$this->user_id, "%$search%", $search]);

      return view("modules.addStudents",
        [
          "courses" => $listCourses,
          "user" => $user,
          "relationships" => $relationships,
          "length" => (int) $length[0]->length,
          "block" => (int) $block,
          "current" => (int) $current,
        ]
      );
    } else {
      return redirect("/");
    }
  }

  public function anyFindUser($search)
  {
    $users = User::where("name", "like", "%" . $search . "%")->orWhere("email", $search)->get();
    return view("user.list-search", ["users" => $users, "i" => 0]);
  }

  public function postStudent()
  {
		if(request()->has('student_id')) {
			$user = User::find(decrypt(request()->get('student_id')));
			$message = "Os dados do aluno foram atualizados com sucesso.";
		} else {
			$user = new User;
			$user->type = "N";
			$message = "Aluno cadastrado com sucesso.";
		}
    $user->enrollment = request()->get("enrollment");
    $user->name = request()->get("name");
    $user->email = strlen(request()->get("email")) ? request()->get("email") : null;
		$user->course = request()->get("course");
		$user->gender = request()->get("gender");
    $user->birthdate = request()->get("date-year") . "-" . request()->get("date-month") . "-" . request()->get("date-day");
    $user->save();

		if(!request()->has('student_id')) {
			$relationship = new Relationship;
			$relationship->user_id = $this->user_id;
			$relationship->friend_id = $user->id;
			$relationship->status = "E";
			$relationship->type = "1";
			$relationship->save();
		}

    return redirect("/user/student")->with("success", $message);
  }

  public function postUnlink()
  {
    $idTeacher = decrypt(request()->get("input-trash"));

    $offers = DB::select("SELECT Courses.name AS course, Periods.name AS period, Classes.class as class, Disciplines.name AS discipline "
      . "FROM Courses, Periods, Classes, Offers, Lectures, Disciplines "
      . "WHERE Courses.institution_id=? AND Courses.id=Periods.course_id AND "
      . "Periods.id=Classes.period_id AND Classes.id=Offers.class_id AND "
      . "Offers.discipline_id=Disciplines.id AND "
      . "Offers.id=Lectures.offer_id AND Lectures.user_id=?", [$this->user_id, $idTeacher]);

    if (count($offers)) {
      $str = "Erro ao desvincular professor, ele está associado a(s) disciplina(s): <br><br>";
      $str .= "<ul class='text-justify list-group'>";
      foreach ($offers as $offer) {
        $str .= "<li class='list-group-item'>$offer->course/$offer->period/$offer->class/$offer->discipline</li>";
      }
      $str .= "</ul>";

      return Redirect::back()->with("error", $str);
    } else {
      Relationship::where('user_id', $this->user_id)
        ->where('friend_id', $idTeacher)
        ->whereType(2)
        ->update(["status" => "D"]);

      return redirect("/user/teacher")->with("success", "Professor excluído dessa Instituição");
    }
  }

  public function getInfouser()
  {
    $user = User::find(decrypt(request()->get("user")));
    $user->enrollment = DB::table('Relationships')->where('user_id', $this->user_id)->where('friend_id', $user->id)->pluck('enrollment');
    $user->password = null;
    return $user;
  }

  public function anyLink($type, $user)
  {
    switch ($type) {
      case "student":
        $type = 1;
        break;
      default:
        return Redirect::back()->with("error", "Cadastro errado.");
    }
    $user = decrypt($user);

    $r = Relationship::where("user_id", $this->user_id)->where("friend_id", $user)->whereType($type)->first();
    if ($r and $r->status == "E") {
      return Redirect::back()->with("error", "Já possui esse relacionamento.");
    } elseif ($r) {
      $r->status = "E";
    } else {
      $r = new Relationship;
      $r->user_id = $this->user_id;
      $r->friend_id = $user;
      $r->type = $type;
    }
    $r->save();

    return Redirect::back()->with("success", "Relacionamento criado com sucesso.");
  }

  public function printScholarReport()
  {
    $data = [];
		$data['units'] = [];
    // Obtém dados da instituição
    $data['institution'] = User::find($this->user_id);

    // Obtém dados do aluno
    $data['student'] = User::find(decrypt(request()->get('u')));

    // Obtém número de matrícula do aluno na instituição
    $e = Relationship::where('user_id', $this->user_id)->where('friend_id', $data['student']->id)->first();
    $data['student']['enrollment'] = $e['enrollment'];

    $disciplines = DB::select("
      SELECT
        Courses.id as course,
        Disciplines.name,
        Offers.id as offer,
        Attends.id as attend,
        Classes.status as statusclasse,
				Units.value as value
      FROM
        Classes, Periods, Courses, Disciplines, Offers, Units, Attends
      WHERE
        Courses.institution_id =  ?
        and Courses.id = Periods.course_id
        and Periods.id = Classes.period_id
        and Classes.schoolYear =  ?
        and Classes.id = Offers.class_id
        and Offers.discipline_id = Disciplines.id
        and Offers.id = Units.offer_id
        and Units.id = Attends.unit_id and Attends.user_id =  ?
				and Units.value IN (?)
				and Classes.status = 'E'
				and Courses.id = ?
      GROUP BY Offers.id",
      [$this->user_id, request()->get('schoolYear'), $data['student']->id, implode(',', request()->get('unit_value')), request()->get('course')]
    );

    if (!$disciplines) {
      return "Não há informações para gerar o boletim.";
    }

		//Variável para acumular os pareceres
		$pareceres = new StdClass;
		$pareceres->disciplines = [];
    foreach ($disciplines as $key => $discipline) {

      // Obtém informações da disciplinas
      $data['disciplines'][$key] = (array) $discipline;

			$pareceres->disciplines[] = $discipline;
			$pareceres->disciplines[$key]->units = [];
			$pareceres->disciplines[$key]->hasParecer = false;

      $units = Offer::find($data['disciplines'][$key]['offer'])->units()->whereIn('value', request()->get('unit_value'))->orderBy('created_at')->get();
      foreach ($units as $key2 => $unit) {
				$pareceres->disciplines[$key]->units[] = $unit;
        // Obtém quantidade de aulas realizadas
        $data['disciplines'][$key][$unit->value]['lessons'] = Offer::find($unit->offer_id)->qtdUnitLessons($unit->value);

        // Obtém quantidade de faltas
        $data['disciplines'][$key][$unit->value]['absenceses'] = Offer::find($unit->offer_id)->qtdUnitAbsences($data['student']['id'], $unit->value);

        // Obtém a média do alunos por disciplina por unidade
        $average = number_format($unit->getAverage($data['student']['id'])[0], 0);
        if ($unit->calculation != 'P') {
          $data['disciplines'][$key][$unit->value]['average'] = ($average > 10) ? number_format($average, 0) : number_format($average, 2);
        }
				else {

					$pareceres->disciplines[$key]->units[$key2]->pareceres = [];
					//Obtém os pareceres
					$attend = Attend::where('unit_id', $unit->id)->where('user_id', $data['student']->id)->first();
					$pareceresTmp = DescriptiveExam::where('attend_id', $attend->id)->get();

					foreach ($pareceresTmp as $parecer) {
						$parecer->exam = Exam::where('id', $parecer->exam_id)->first(['title', 'type', 'date']);
						$parecer->exam->type = $this->typesExams($parecer->exam->type);
					}
					if(!empty($pareceresTmp)) {
						$pareceres->disciplines[$key]->hasParecer = true;
					}

					//Guarda os pareceres para enviar para view
					$pareceres->disciplines[$key]->units[$key2]->pareceres = $pareceresTmp;

          $data['disciplines'][$key][$unit->value]['average'] = '<small>Parecer<br>descritivo</small>';
        }

        $examRecovery = $unit->getRecovery();

        // Verifica se há prova de recuperação
        if ($examRecovery) {
          $attend = Attend::where('unit_id', $unit->id)->where('user_id', $data['student']['id'])->first();
          $recovery = ExamsValue::where('attend_id', $attend->id)->where('exam_id', $examRecovery->id)->first();
          $data['disciplines'][$key][$unit->value]['recovery'] = isset($recovery) && $recovery->value ? $recovery->value : '--';
        }
      }
			$data['units'] = count($data['units']) < count($units) ? $units : $data['units'];
    }
		//Guarda pareceres
		$data['pareceres'] = $pareceres;

    // Obtém dados do curso
    $data['course'] = Course::find($disciplines[0]->course);

    // Obtém dados da turma
    $data['classe'] = Offer::find($disciplines[0]->offer)->classe;

    $pdf = PDF::loadView('reports.arroio_dos_ratos-rs.final_result', ['data' => $data]);
    return $pdf->stream();
  }

	public function typesExams($type) {
		$typesExams = [
			"Prova Dissertativa Individual",
			"Prova Dissertativa em Grupo",
			"Prova Objetiva Individual",
			"Prova Objetiva em Grupo",
			"Trabalho Dissertativo Individual",
			"Trabalho Dissertativo em Grupo",
			"Apresentação de Seminário",
			"Projeto",
			"Produção Visual",
			"Pesquisa de Campo",
			"Texto Dissertativo",
			"Avaliação Prática",
			"Outros"
		];
		return $typesExams[$type];
	}
}
