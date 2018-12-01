<?php namespace App;

class Bind extends \Moloquent {

	public $timestamps = false;

	public function discipline() {
    return $this->hasOne('Discipline', 'idDiscipline');
  }
}
