<?php namespace App;

class Exam extends \Moloquent
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'unit_id',
		'title',
		'type', // Tipo de avaliação: E = exams; L = list; P = projects;...
		'aval', // A = Avaliação, R = Recuperação da Unidade
		'weight',
		'date',
		'comments',
		'status',
	];

	/**
	 * The model's default values for attributes.
	 *
	 * @var array
	 */
	protected $attributes = [
		'status' => 'E',
		'weight' => 1,
	];

	public function setWeightAttribute($value)
	{
		$this->attributes['weight'] = (int) $value;
	}

	public function unit()
	{
		return $this->belongsTo('Unit', 'unit_id');
	}

	public function descriptive_exams()
	{
		$descriptive_exams = $this->hasMany('DescriptiveExam', 'exam_id')->get();

		foreach ($descriptive_exams as $key => $descriptive_exam) {
			$descriptive_exams[$key]['student'] = $descriptive_exam->student();
		}

		return $descriptive_exams;
	}
}
