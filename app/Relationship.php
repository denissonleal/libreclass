<?php namespace App;

class Relationship extends \Moloquent {

  protected $fillable = ['user_id', 'friend_id', 'type'];

  public function getUser() {
    return User::find($this->user_id);
  }

  public function getFriend() {
    return User::find($this->friend_id);
  }
}
