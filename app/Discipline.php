<?php namespace App;

class Discipline extends \Moloquent {
  protected $fillable = ['name', 'period_id'];

  public function period() {
    return $this->belongsTo('Period', 'period_id');
  }

  public function getPeriod() {
    return Period::find($this->period_id);
  }
}
