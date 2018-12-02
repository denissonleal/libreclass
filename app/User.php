<?php namespace App;

use Jenssegers\Mongodb\Eloquent\SoftDeletes;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends \Moloquent implements
	AuthenticatableContract,
	AuthorizableContract,
	CanResetPasswordContract
{
	use SoftDeletes;
	use Authenticatable, Authorizable, CanResetPassword;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'email',
		'password',
		'name',
		'type', // P = Professor; A = Aluno; N = aluno não cadastrado; M = professor não cadastrado
		'gender', // F = feminino; M = masculino
		'birthdate',
		'institution', // Nome da Instituição de ensino
		'uee',
		'course', // Cursos realizados pelo usuário (formação)
		'formation', // Nível de formação acadêmica(Graduated, Master, PhD...)
		'cadastre', // T = Temporário, W = aguardando, N = Normal, G = Google, F = Facebook
		'city_id',
		'street',
		'photo',
		'enrollment', // matricula
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password', 'remember_token',
	];

	/**
	 * The model's default values for attributes.
	 *
	 * @var array
	 */
	protected $attributes = [
		'type' => 'P',
		'cadastre' => 'N',
		'photo' => '/images/user-photo-default.jpg',
		'formation' => '0',
	];

	public function setNameAttribute($value)
	{
		$this->attributes['name'] = titleCase(trimpp($value));
	}

	public function setEmailAttribute($value)
	{
		$this->attributes['email'] = mb_strtolower(trimpp($value));
	}

	public function setTypeAttribute($value)
	{
		$this->attributes['type'] = mb_strtoupper(trimpp($value));
	}

	public function courses()
	{
		return $this->hasMany(Course::class, 'institution_id');
	}

	public function printLocation()
	{
		$city = City::find($this->city_id);
		if (!$city) {
			return "";
		}

		$state = State::find($city->state_id);
		$country = Country::find($state->country_id);

		return "$city->name, $state->name, $country->name";
	}

	public function printCityState()
	{
		$city = City::find($this->city_id);
		if (!$city) {
			return "";
		}

		$state = State::find($city->state_id);

		return "$city->name - $state->name";
	}
}
