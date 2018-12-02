<?php namespace App;

class State extends \Moloquent
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'country_id',
		'name',
		'short',
	];
}
