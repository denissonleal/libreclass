<?php namespace App;

class Frequency extends \Moloquent
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'attend_id', // Id do relacionamento cursa
		'lesson_id', // Id da aula
		'value', // P = Presente; F = Falta;
	];

	public static function getValue($user, $lesson)
	{
		$attend_ids = Attend::where('user_id', $user)->get(['_id']);

		$value = Frequency::where('exam_id', $lesson)
			->whereIn('attend_id', $attend_ids)
			->first(['value']);

		return $value ? $value->value : '';
	}
}
