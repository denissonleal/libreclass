<?php namespace App;

class Unit extends \Moloquent
{

  protected $fillable = ['offer_id'];

  public function offer()
  {
    return $this->belongsTo('Offer', 'offer_id');
  }

  public function getOffer()
  {
    return Offer::find($this->offer_id);
  }

  public function getAverage($student)
  {
    $exams = Exam::where("idUnit", $this->id)->whereAval("A")->whereStatus("E")->get();
    $attend = Attend::where("idUnit", $this->id)->where("user_id", $student)->first();
    if (!$attend) {
      return null;
    }

    $out = [null, null];
    $sum = 0.;
    $weight = 0.;
    // echo count($exams) . " - ";
    foreach ($exams as $exam) {
      $value = ExamsValue::where("idExam", $exam->id)->where("idAttend", $attend->id)->first();
      // echo " $value ";
      if ($value) {
        $sum += $value->value * ($this->calculation == "W" ? $exam->weight : 1);
      }
      /* so multiplica pelo peso quando for média ponderada */
      $weight += $exam->weight;
    }

    // echo $this->calculation . " $sum ";
    /* tipo de calculo da média */
    if ($this->calculation == "A" and count($exams)) {
      $out[0] = $sum / count($exams);
    } elseif ($this->calculation == "W" and $weight > 0) {
      $out[0] = $sum / $weight;
    } elseif ($this->calculation == "S") {
      $out[0] = $sum;
    }

    $final = Exam::where("idUnit", $this->id)->whereAval("R")->first();
    if ($final) {
      $value = ExamsValue::where("idExam", $final->id)->where("idAttend", $attend->id)->first();
      if ($value) {
        $out[1] = $value->value;
      }
    }

    // print_r($out);exit;

    return $out;
  }

  public function getLessons()
  {
    return Lesson::where("idUnit", $this->id)->whereStatus("E")->get();
  }

  public function getLessonsToPdf()
  {
    return Lesson::where("idUnit", $this->id)->whereStatus("E")->orderBy("date", "asc")->orderBy("id", "asc")->get();
  }

  public function countLessons()
  {
    return Lesson::where("idUnit", $this->id)->whereStatus("E")->count();
  }

  public function getExams()
  {
    return Exam::where("idUnit", $this->id)->whereStatus("E")->whereAval("A")->get();
  }

  public function getRecovery()
  {
    return Exam::where("idUnit", $this->id)->whereStatus("E")->whereAval("R")->first();
  }

}
