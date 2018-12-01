<?php namespace App\Http\Controllers;

class ClassroomController extends Controller
{
  private $user_id;

  public function __construct()
  {
    $id = session("user");
    if ($id == null || $id == "") {
      $this->user_id = false;
    } else {
      $this->user_id = decrypt($id);
    }
  }

  public function getIndex()
  {
    if (session("redirect")) {
      return redirect(session("redirect"));
    }
    $user = User::find($this->user_id);
    Session::put("type", $user->type);
    return view("classrooms.home", ["user" => $user]);
  }

  public function getCampus()
  {
    $user = User::find($this->user_id);
    return view("classrooms.campus", ["user" => $user]);
  }

}