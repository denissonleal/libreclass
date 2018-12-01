<?php

class SyncController extends \BaseController {

  public function __construct()
  {
    if ( Session::has("user") )
      $this->user = User::find(decrypt(Session::get("user")));
    else
      $this->user = false;
  }

  public function getIndex()
  {
    $user = $this->user;
    $keyExams = [];
    $keyLessons = [];

    if (Input::has("lb"))
      Session::put("lb", Input::get("lb"));


    if ($user)
    {
      Session::forget("redirect");
      $data = $this->user;
      $data->id = encrypt($data->id); //crypt user
      $data->download = date("H:i:s - d/m/Y");
      unset($data->password);
//       return $data;
      if ($user->type == "P")
      {
        /*Getting professor's offers*/
        $lectures = Lecture::where('user_id', decrypt($user->id))->orderBy("order")->get();

        $keyAttend = []; /* array para salvar chave, para
         * que seja igual em Attends e em ExamsValues.
         * Cada vez que criptografa sai uma chave diferente. */

        foreach ( $lectures as $lecture ) {
          $offer = Offer::find($lecture->offer_id);
          $units = Unit::where('offer_id', $offer->id)->get();
          $offer->id = encrypt($offer->id); //crypt offer
          $lecture->offer_id = $offer->id;
          $lecture->user_id = $data->id;
          foreach ( $units as $unit ) {
            $exams = Exam::where('idUnit', $unit->id)->get();
            $lessons = Lesson::where('idUnit', $unit->id)->get();
            $attends = Attend::where('idUnit', $unit->id)->get();
            $unit->id = encrypt($unit->id); //crypt unit
            $unit->offer_id = $offer->id;

            foreach ( $attends as $attend ) {
              $keyAttend[$attend->id] = encrypt($attend->id);
              $attend->id = $keyAttend[$attend->id]; //crypt attend
              $attend->idUnit = $unit->id;
              $attend->user = User::find($attend->user_id);
              $attend->user->id = encrypt($attend->user->id);
              $attend->user_id = $attend->user->id;
              unset($attend->user->password);
              unset($attend->user->email);
              unset($attend->user->photo);
              unset($attend->user->idCity);
              unset($attend->user->cadastre);
              unset($attend->user->formation);
              unset($attend->user->course);
              unset($attend->user->institution);
              unset($attend->user->updated_at);
              unset($attend->user->created_at);
            }
            $unit->attends = $attends;

            foreach ( $exams as $exam ) {
              $examsValues = ExamsValue::where('idExam', $exam->id)->get();
              $exam->id = encrypt($exam->id);
              $exam->idUnit = $unit->id;
              foreach ( $examsValues as $examValue) {
                $examValue->idAttend = $keyAttend[$examValue->idAttend];
                $examValue->idExam = $exam->id;
              }
              $exam->examsValues = $examsValues;
            }
            $unit->exams = $exams;

            foreach ( $lessons as $lesson ) {
              $frequencies = Frequency::where('idLesson', $lesson->id)->get();
              $lesson->id = encrypt($lesson->id);
              $lesson->idUnit = $unit->id;
              foreach ( $frequencies as $frequency ) {
                $frequency->idAttend = $keyAttend[$frequency->idAttend];
                $frequency->idLesson = $lesson->id;
              }
              $lesson->frequencies = $frequencies;
            }
            $unit->lessons = $lessons;
          }

          $offer->discipline = Discipline::find($offer->idDiscipline);
          $offer->discipline->id = encrypt($offer->discipline->id); //crypt discipline
          $offer->class = Classe::find($offer->class_id);
          $offer->class->id = encrypt($offer->class->id); //crypt class
          $offer->period = Period::find($offer->discipline->period_id);
          $offer->period->id = encrypt($offer->period->id); //crypt period
          $offer->discipline->period_id = $offer->period->id;
          $offer->class->period_id = $offer->period->id;
          $offer->period->course = Course::find($offer->period->course_id);
          $offer->period->course->id = encrypt($offer->period->course->id); //crypt course
          $offer->period->course_id = $offer->period->course->id;
          $offer->period->course->institution = User::find($offer->period->course->institution_id);
          $offer->period->course->institution->id = encrypt($offer->period->course->institution->id); //crypt institution
          $offer->period->course->institution_id =  $offer->period->course->institution->id;
          /*crypt offer left*/
          $offer->units = $units;
          $offer->idDiscipline = $offer->discipline->id;
          $offer->class_id = $offer->class->id;
          $lecture->offer = $offer;
  //        return $lecture;
        }
        $data->lectures = $lectures;
        return View::make("modules.sync.login", ["data" => $data]);
        //return $data;
      }

      return Redirect::to("/sync/error")
                           ->with("error", "Erro ao syncronizar. Usuário deve ser professor.");
    }
    else
    {
      Session::put("redirect", "sync");
      return Redirect::to("/login");
    }
  }

  public function postReceive()
  {
    Session::put("data", Input::get("data"));
    Session::put("lb", Input::get("lb"));
    if ($this->user)
    {
      return Redirect::to("/sync/receive");
    }
    else
    {
      Session::put("redirect", "sync/receive");
      return Redirect::to("/login");
    }
  }

  public function getReceive()
  {
    if( !Input::has("confirm") )
    {
      return View::make("modules.sync.send", ["data" => $this->user ]);
    }

    Session::forget("redirect");
    $data = json_decode(Session::get("data"));

    if(decrypt($data->id) != $this->user->id)
          return Redirect::to("/sync/error")
                 ->with("erro", "Erro ao syncronizar. Incompatiblilidade de usuários [" . $this->user->email . " != $data->email]");

    foreach( $data->lectures as $lecture )
    {
      foreach( $lecture->offer->units as $unit )
      {
        $unit->id = decrypt($unit->id);
        $valid = DB::select("SELECT COUNT(*) as valid FROM Lectures, Offers, Units "
                            . "WHERE Lectures.user_id=? AND Lectures.offer_id=Units.offer_id AND Units.id=?",
                            [$this->user->id, $unit->id])[0]->valid;
        if($valid == 0)
          return Redirect::to("/sync/error")
                         ->with("error", "Erro ao syncronizar. Arquivo foi modificado de forma maliciosa. [units]")
                         ->with("email", $this->user->email);

        foreach($unit->lessons as $json_lesson)
        {
          $lesson = null;
          if(is_numeric($json_lesson->id))
          {
            $lesson = new Lesson;
            $lesson->idUnit = $unit->id;
          }
          else
            $lesson = Lesson::find(decrypt($json_lesson->id));

          if ( $lesson->idUnit != $unit->id )
            return Redirect::to("/sync/error")
                           ->with("error", "Erro ao syncronizar. Arquivo foi modificado de forma maliciosa. [lesson]")
                           ->with("email", $this->user->email);

          $lesson->date = $json_lesson->date;
          $lesson->title = $json_lesson->title;
          $lesson->description = $json_lesson->description;
          $lesson->goals = $json_lesson->goals;
          $lesson->content = $json_lesson->content;
          $lesson->methodology = $json_lesson->methodology;
          $lesson->keyworks = $json_lesson->keyworks;
          $lesson->estimatedTime = $json_lesson->estimatedTime;
          $lesson->bibliography = $json_lesson->bibliography;
          $lesson->valuation = $json_lesson->valuation;
          $lesson->notes = $json_lesson->notes;
          $lesson->save();
          foreach ($json_lesson->frequencies as $json_frequency)
            if ( !Frequency::where('idAttend', decrypt($json_frequency->idAttend))
                            ->where('idLesson', $lesson->id)
                            ->update(["value" => $json_frequency->value]) )
            {
              $frequency = new Frequency;
              $frequency->idAttend = decrypt($json_frequency->idAttend);
              $frequency->idLesson = $lesson->id;
              $frequency->value = $json_frequency->value;
              $frequency->save();
            }
        }

        foreach($unit->exams as $json_exam)
        {
          $exam = null;
          if(is_numeric($json_exam->id))
          {
            $exam = new Exam;
            $exam->idUnit = $unit->id;
          }
          else
            $exam = Exam::find(decrypt($json_exam->id));

          if ( $exam->idUnit != $unit->id )
            return Redirect::to("/sync/error")
                           ->with("error", "Erro ao syncronizar. Arquivo foi modificado de forma maliciosa. [exam]")
                           ->with("email", $this->user->email);

          $exam->date = $json_exam->date;
          $exam->title = $json_exam->title;
          $exam->type = $json_exam->type;
          $exam->aval = $json_exam->aval;
          $exam->weight = $json_exam->weight;
          $exam->comments = $json_exam->comments;
          $exam->save();

          foreach ($json_exam->examsValues as $json_value )
            if ( !ExamsValue::where('idAttend', decrypt($json_value->idAttend))
                            ->where('idExam', $exam->id)
                            ->update(array('value' => $json_value->value)) )
            {
              $value = new ExamsValue;
              $value->idAttend = decrypt($json_value->idAttend);
              $value->idExam = $exam->id;
              $value->value = $json_value->value;
              $value->save();
            }
        }
      }
    }
    return Redirect::to("/sync");
  }

  public function getError()
  {
    if( Session::has("email") )
      Mail::send('email.alert', ["msg" => Session::get("error"), "email" => Session::get("email") ], function($message)
      {
        $message->to( "suporte@sysvale.com", "Suporte LibreClass" )
                ->subject("Tentativa de burlar o sistema");
      });

    return View::make("modules.sync.error", ["data" => $this->user, "error" => Session::get("error")]);
  }
}
