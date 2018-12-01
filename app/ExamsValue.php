<?php namespace App;

class ExamsValue extends \Moloquent {


  public static function getValue($user, $exam)
  {
    $out = DB::select("select ExamsValues.value "
                      . "from ExamsValues, Attends "
                      . "where ExamsValues.idExam=? and ExamsValues.idAttend=Attends.id and Attends.user_id=?",
                        [$exam, $user]);

    return count($out) ? $out[0]->value : "";
  }

}