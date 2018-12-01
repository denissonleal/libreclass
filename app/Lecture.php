<?php namespace App;

class Lecture extends \Moloquent {

	protected $fillable = ['idUser', 'idOffer'];

  public function getUser() {
    return User::find($this->idUser);
  }

  public function getOffer() {
    return Offer::find($this->idOffer);
  }

	public function offer() {
    return $this->belongsTo('Offer', 'idOffer');
  }

}
