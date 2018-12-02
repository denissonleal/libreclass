<?php namespace App;

class Ctrl extends \Moloquent
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'controller_id', // Id do usuário controlador
		'subject_id', // Id do usuário controlado
		'type', // Usuários (ex.: instituição) podem cadastrar usuários (ex.: professor); IP = Instituição controla Professor; ...
	];
}
