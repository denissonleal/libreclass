<?php namespace App;

class Relationship extends \Moloquent {

  protected $fillable = ['idUser', 'idFriend', 'type'];

  public function getUser() {
    return User::find($this->idUser);
  }

  public function getFriend() {
    return User::find($this->idFriend);
  }
}
