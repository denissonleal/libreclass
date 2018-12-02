<?php namespace App;

class Discipline extends \Moloquent
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'period_id',
		'name', // Nome da disciplina
		'ementa', // ementa da disciplina
		'status', // E = Enabled; D = Disabled; F = Finalized;
	];

	/**
	 * The model's default values for attributes.
	 *
	 * @var array
	 */
	protected $attributes = [
		'status' => 'E',
	];

	public function setNameAttribute($value)
	{
		$this->attributes['name'] = titleCase(trimpp($value));
	}

	public function period()
	{
		return $this->belongsTo(Period::class);
	}

	public function getPeriod()
	{
		return Period::find($this->period_id);
	}
}
