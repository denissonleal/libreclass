<?php namespace App;

class Period extends \Moloquent {

	public $fillable = [
	];

	public function disciplines()
	{
		return $this->hasMany(Discipline::class);
	}
}
