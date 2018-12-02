<?php namespace App;

class Suggestion extends \Moloquent
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'user_id',
		'title',
		'value', // S = sugestões; B = bugs
		'description',
	];

	/**
	 * The model's default values for attributes.
	 *
	 * @var array
	 */
	protected $attributes = [
		'value' => 'S',
	];
}
