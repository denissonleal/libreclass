<?php namespace App\Http\Controllers;

class SocialController extends Controller
{
  private $user_id;

  public function __construct()
  {
    $id = session("user");
    if ( $id == null || $id == "" )
      $this->user_id = false;
    else
      $this->user_id = decrypt($id);
  }

  public function getIndex()
  {
    if ( session("redirect") )
      return redirect(session("redirect"));

    $user = User::find($this->user_id);
    Session::put("type", $user->type);

    return view("social.home", ["user" => $user]);
  }

  public function postQuestion()
  {
    //~ print_r(request()->all());
    foreach( request()->all() as $key => $value )
      return User::whereId($this->user_id)->update([$key => $value]);
  }

  public function postSuggestion()
  {
    $suggestion = new Suggestion;
    $suggestion->user_id      = $this->user_id;
    $suggestion->title       = request()->get("title");
    $suggestion->value       = request()->get("value");
    $suggestion->description = request()->get("description");
    $suggestion->save();

    $user = User::find($this->user_id);

    Mail::send('email.suporte', ["descricao" => request()->get("description"), "email" => $user->email, "title" => request()->get("title")], function($message)
    {
      $op = ["B" => "Bugson", "O" => "Outros", "S" => "Sugestão"];
      $message->to( "suporte@sysvale.com", "Suporte" )
              ->subject("LibreClass Suporte - " . $op[request()->get("value")]);
    });

    return Redirect::back()->with("success", "Obrigado pela sua mensagem. Nossa equipe irá analisar e responderá o mais breve possível.");
  }



}
