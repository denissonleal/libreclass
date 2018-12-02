<?php namespace App;

class Frequency extends \Moloquent
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'offer_id',
		'user_id',
		'value',
	];

	public function setValueAttribute($value)
	{
		$this->attributes['value'] = (float) $value;
	}

	public static function getValue($user, $lesson)
	{
		$out = DB::select("select Frequencies.value "
			. "from Frequencies, Attends "
			. "where Frequencies.lesson_id=? and Frequencies.attend_id=Attends.id and Attends.user_id=?",
			[$lesson, $user]);

		return count($out) ? $out[0]->value : "";
	}
}
