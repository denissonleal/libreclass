<?php namespace App;

class Classe extends \Moloquent
{

  protected $fillable = ['name', 'idPeriod', 'class'];

  public function period()
  {
    return $this->belongsTo('Period', 'idPeriod');
  }

  public function getPeriod()
  {
    return Period::find($this->idPeriod);
  }

  public function fullName()
  {
    return "$this->name $this->class";
  }
}
