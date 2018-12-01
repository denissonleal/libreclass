<?php

class DisciplinesController extends \BaseController {

  /**
   * Armazena o ID do usuário
   * @var type num
   */
  private $user_id;

  public function __construct()
  {
    $id = Session::get("user");
    if ($id == null || $id == "") {
      $this->user_id = false;
    }
    else {
      $this->user_id = decrypt($id);
    }
  }

  /**
   * Construção da página inicial
   * @return type redirect
   */
  public function getIndex()
  {
    if ($this->user_id) {
      $user = User::find($this->user_id);
      $courses = Course::where("institution_id", $this->user_id)->whereStatus("E")->orderBy("name")->get();
      $listCourses = [];
      foreach ($courses as $course) {
        $listCourses[encrypt($course->id)] = $course->name;
      }
      return View::make("social.disciplines", ["listCourses" => $listCourses, "user" => $user]);
    }
    else {
//      return Redirect::guest("/");
    }
  }

  public function postSave()
  {
    $course = Course::find(decrypt(Input::get("course")));
    if ($this->user_id == $course->institution_id) {
      $period = Period::where("course_id", $course->id )->whereId(decrypt(Input::get("period")))->first();
      $discipline = null;
      if (strlen(Input::get("discipline"))) {
        $discipline = Discipline::find(decrypt(Input::get("discipline")));
      }
      else {
        $discipline = new Discipline;
      }
      $discipline->period_id = $period->id;
      $discipline->name = Input::get("name");
      $discipline->ementa = Input::get("ementa");
      $discipline->save();
      return Redirect::to("/disciplines")->with("success", "Disciplina inserida com sucesso.");
    }
    else {
      return Redirect::to("/disciplines")->with("error", "Não foi possível inserir a disciplina.");
    }
    var_dump(Input::all());
  }

  public function postDelete()
  {
    //~ return Input::all();

    $discipline = Discipline::find(decrypt(Input::get("input-trash")));
    $offers = DB::select("SELECT Offers.id, Classes.name
                            FROM Offers, Classes
                             WHERE Classes.status = 'E' AND
                             Offers.discipline_id=? AND
                             Offers.class_id=Classes.id", [$discipline->id]);

    if(count($offers)) {
      return Redirect::to("/disciplines")->with("error", "Não foi possível excluir. <br>Disciplina vinculada à turma <b>". $offers[0]->name . "</b>");
    }

    if ($discipline) {
      $discipline->status = "D";
      $discipline->save();
      return Redirect::to("/disciplines")->with("success", "Excluído com sucesso.");
    }
    else {
      return Redirect::to("/disciplines")->with("error", "Não foi possível excluir a disciplina.");
    }
  }

  public function getDiscipline()
  {
    $discipline = Discipline::find(decrypt(Input::get("discipline")));
    return $discipline;
  }

  public function postEdit()
  {
    $discipline = Discipline::find(decrypt(Input::get("discipline")));
    if (!isset($discipline) || empty($discipline)) {
      return Redirect::back()->with("error", "Não foi possível editar a disciplina");
    } else {
      $discipline->name = Input::get("name");
      $discipline->ementa = Input::get("ementa");
      $discipline->save();
      return Redirect::back()->with("success", "Disciplina editada com sucesso!");
    }
  }

  /**
   * Lista os periodos para mostrar em um select
   *
   * @return array [id=>value]
   */
  public function postListperiods()
  {
    $periods = Period::where("course_id", decrypt(Input::get("course")))->whereStatus("E")->get();
    foreach( $periods as $period )
      $period->id = encrypt($period->id);

    return $periods;
  }

  public function anyList()
  {
    if(Input::get("course")) {
      $disciplines = DB::select("SELECT Disciplines.id AS id, Disciplines.name AS name, Periods.name AS period FROM Disciplines, Periods WHERE period_id = Periods.id AND course_id = ? AND Disciplines.status = 'E'", [decrypt(Input::get("course"))]);
      return View::make("social.disciplines.list", [ "disciplines" => $disciplines ]);
    }
  }

  public function getEmenta() {

      $discipline = Discipline::find(decrypt(Input::get("offer")));

      return $discipline;
  }

}
