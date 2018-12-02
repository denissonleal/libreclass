<?php namespace App;

class ExamsValue extends \Moloquent
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'attend_id',
		'exam_id',
		'value',
	];

	public static function getValue($user, $exam)
	{
		$out = DB::select("select ExamsValues.value "
			. "from ExamsValues, Attends "
			. "where ExamsValues.exam_id=? and ExamsValues.attend_id=Attends.id and Attends.user_id=?",
				[$exam, $user]);

		return count($out) ? $out[0]->value : "";
	}

}
