<?php namespace App;

class Course extends \Moloquent {

	public $fillable = [
		'institution_id',
		'name',
		'type', // dados do csv
		'modality', // dados do csv
		'absent_percent',
		'average',
		'average_final',
		'status', // E - enable, D - disable
		'curricular_profile',
	];

	protected $fillable = [
		'status' => 'E',
	];

	public function setNameAttribute($value)
	{
		$this->attributes['name'] = titleCase(trimpp($value));
	}

	public function setAbsentPercentAttribute($value)
	{
		$this->attributes['absent_percent'] = (float) $value;
	}

	public function setAverageAttribute($value)
	{
		$this->attributes['average'] = (float) $value;
	}

	public function setAverageFinalAttribute($value)
	{
		$this->attributes['average_final'] = (float) $value;
	}
}
