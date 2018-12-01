<?php namespace App;

class Lecture extends \Moloquent {

	protected $fillable = ['user_id', 'offer_id'];

  public function getUser() {
    return User::find($this->user_id);
  }

  public function getOffer() {
    return Offer::find($this->offer_id);
  }

	public function offer() {
    return $this->belongsTo('Offer', 'offer_id');
  }

}
