<?php namespace App;

class Classe extends \Moloquent
{

  protected $fillable = ['name', 'period_id', 'class'];

  public function period()
  {
    return $this->belongsTo('Period');
  }

  public function getPeriod()
  {
    return Period::find($this->period_id);
  }

  public function fullName()
  {
    return "$this->name $this->class";
  }
}
