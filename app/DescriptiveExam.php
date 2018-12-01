<?php namespace App;

class DescriptiveExam extends \Moloquent
{


  public function student()
  {
    return $this->belongsTo('Attend', 'attend_id')->first()->getUser();
  }

}
