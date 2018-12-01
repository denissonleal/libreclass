<?php namespace App;

class Lecture extends \Moloquent {

	protected $fillable = ['user_id', 'idOffer'];

  public function getUser() {
    return User::find($this->user_id);
  }

  public function getOffer() {
    return Offer::find($this->idOffer);
  }

	public function offer() {
    return $this->belongsTo('Offer', 'idOffer');
  }

}
