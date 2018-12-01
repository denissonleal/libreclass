<?php

class ClassroomController extends \BaseController {

  private $user_id;

  public function __construct() {
    $id = Session::get("user");
    if ($id == null || $id == "") {
      $this->user_id = false;
    } else {
      $this->user_id = decrypt($id);
    }
  }

  public function getIndex() {
    if (Session::has("redirect")) {
      return Redirect::to(Session::get("redirect"));
    }
    $user = User::find($this->user_id);
    Session::put("type", $user->type);
    return view("classrooms.home", ["user" => $user]);
  }

  public function getCampus() {
    $user = User::find($this->user_id);
    return view("classrooms.campus", ["user" => $user]);
  }

}