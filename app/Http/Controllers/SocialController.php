<?php namespace App\Http\Controllers;

class SocialController extends Controller
{
  private $user_id;

  public function __construct()
  {
    $id = Session::get("user");
    if ( $id == null || $id == "" )
      $this->user_id = false;
    else
      $this->user_id = decrypt($id);
  }

  public function getIndex()
  {
    if ( Session::has("redirect") )
      return redirect(Session::get("redirect"));

    $user = User::find($this->user_id);
    Session::put("type", $user->type);

    return view("social.home", ["user" => $user]);
  }

  public function postQuestion()
  {
    //~ print_r(Input::all());
    foreach( Input::all() as $key => $value )
      return User::whereId($this->user_id)->update([$key => $value]);
  }

  public function postSuggestion()
  {
    $suggestion = new Suggestion;
    $suggestion->user_id      = $this->user_id;
    $suggestion->title       = Input::get("title");
    $suggestion->value       = Input::get("value");
    $suggestion->description = Input::get("description");
    $suggestion->save();

    $user = User::find($this->user_id);

    Mail::send('email.suporte', ["descricao" => Input::get("description"), "email" => $user->email, "title" => Input::get("title")], function($message)
    {
      $op = ["B" => "Bugson", "O" => "Outros", "S" => "Sugestão"];
      $message->to( "suporte@sysvale.com", "Suporte" )
              ->subject("LibreClass Suporte - " . $op[Input::get("value")]);
    });

    return Redirect::back()->with("success", "Obrigado pela sua mensagem. Nossa equipe irá analisar e responderá o mais breve possível.");
  }



}
