<?php namespace App;

class Frequency extends \Moloquent
{


  public static function getValue($user, $lesson)
  {
    $out = DB::select("select Frequencies.value "
      . "from Frequencies, Attends "
      . "where Frequencies.lesson_id=? and Frequencies.idAttend=Attends.id and Attends.user_id=?",
      [$lesson, $user]);

    return count($out) ? $out[0]->value : "";
  }
}
