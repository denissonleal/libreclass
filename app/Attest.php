<?php namespace App;

class Attest extends \Moloquent
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'institution_id',
		'student_id',
		'date',
		'days',
		'description',
	];

	public function setDaysAttribute($value)
	{
		$this->attributes['days'] = (int) $value;
	}
}
