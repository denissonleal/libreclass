<?php namespace App;

class DescriptiveExam extends \Illuminate\Database\Eloquent\Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'attend_id',
		'exam_id',
		'description',
		'approved',
	];

	public function student()
	{
		return $this->belongsTo('Attend', 'attend_id')->first()->getUser();
	}
}
