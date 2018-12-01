<?php namespace App\Http\Controllers;

class HelpController extends Controller
{

  public function getView($rota) {
    $bladeFile = "help.$rota";
    return view($bladeFile);
  }

}
