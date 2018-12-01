<?php namespace App;

class Offer extends \Moloquent
{

  protected $fillable = ['class_id', 'discipline_id', 'classroom', 'day_period', 'grouping'];

  public function master()
  {
    return $this->belongsTo('Offer', 'offer_id');
  }

  public function slaves()
  {
    return $this->hasMany('Offer', 'offer_id');
  }

  public function discipline()
  {
    return $this->belongsTo('Discipline', 'discipline_id');
  }

  public function units()
  {
    return $this->hasMany('Unit', 'offer_id');
  }

  public function classe()
  {
    return $this->belongsTo('Classe', 'class_id');
  }

  public function getDiscipline()
  {
    return Discipline::find($this->discipline_id);
  }

  public function getClass()
  {
    return Classe::find($this->class_id);
  }

  public function getFirstUnit()
  {
    return Unit::where("offer_id", $this->id)->first();
  }

  public function getLastUnit()
  {
    return Unit::where("offer_id", $this->id)->orderBy("value", "desc")->first();
  }

  public function getUnits()
  {
    return Unit::where("offer_id", $this->id)->get();
  }

  public function getLectures()
  {
    return Lecture::where("offer_id", $this->id)->first();
  }

  public function getAllLectures()
  {
    return Lecture::where("offer_id", $this->id)->get();
  }

  public function qtdAbsences($idStudent)
  {
    return DB::select("SELECT COUNT(*) as 'qtd'
                        FROM Units, Attends, Lessons, Frequencies
                        WHERE Units.offer_id=? AND
                              Units.id=Lessons.unit_id AND
                              Lessons.id=Frequencies.lesson_id AND
                              Lessons.deleted_at IS NULL AND
                              Frequencies.idAttend=Attends.id AND
                              Frequencies.value='F' AND
                              Attends.user_id=?", [$this->id, $idStudent])[0]->qtd;
  }

  public function qtdUnitAbsences($idStudent, $unitValue)
  {
    return DB::select("SELECT COUNT(*) as 'qtd'
                        FROM Units, Attends, Lessons, Frequencies
                        WHERE Units.offer_id = ? AND
                              Units.value = ? AND
                              Units.id = Lessons.unit_id AND
                              Lessons.id = Frequencies.lesson_id AND
                              Lessons.deleted_at IS NULL AND
                              Frequencies.idAttend = Attends.id AND
                              Frequencies.value = 'F' AND
                              Attends.user_id = ?", [$this->id, $unitValue, $idStudent])[0]->qtd;
  }

  public function qtdLessons()
  {
    return DB::select("SELECT COUNT(*) as 'qtd'
                        FROM Units, Lessons
                        WHERE Units.offer_id=? AND
                              Units.id=Lessons.unit_id AND
                              Lessons.deleted_at IS NULL", [$this->id])[0]->qtd;
  }

	public function lessons()
	{
		return DB::select("SELECT *
                        FROM Units, Lessons
                        WHERE Units.offer_id=? AND
                              Units.id=Lessons.unit_id AND
                              Lessons.deleted_at IS NULL", [$this->id]);
	}

  public function qtdUnitLessons($unitValue)
  {
    return DB::select("SELECT COUNT(*) as 'qtd'
                        FROM Units, Lessons
                        WHERE Units.offer_id=? AND
                              Units.value=? AND
                              Units.id=Lessons.unit_id AND
                              Lessons.deleted_at IS NULL", [$this->id, $unitValue])[0]->qtd;
  }

  public function getCourse()
  {
    $course = DB::select("SELECT Periods.course_id FROM Classes, Periods WHERE ?=Classes.id AND Classes.period_id=Periods.id", [$this->class_id])[0]->course_id;
    return Course::find($course);
  }

  public function getTeachers()
  {
    $teachers = [];
    $lectures = Lecture::where("offer_id", $this->id)->get();
    foreach ($lectures as $lecture) {
      $teachers[] = $lecture->getUser()->name;
    }
    return $teachers;
  }
}
