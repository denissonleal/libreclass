<?php namespace App\Http\Controllers;

class SocialController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth.type:I');
  }

  public function getIndex()
  {
    if ( session("redirect") )
      return redirect(session("redirect"));

    $user = auth()->user();
    Session::put("type", $user->type);

    return view("social.home", ["user" => $user]);
  }

  public function postQuestion()
  {
    //~ print_r(request()->all());
    foreach( request()->all() as $key => $value )
      return User::whereId(auth()->id())->update([$key => $value]);
  }

  public function postSuggestion()
  {
    $suggestion = new Suggestion;
    $suggestion->user_id      = auth()->id();
    $suggestion->title       = request()->get("title");
    $suggestion->value       = request()->get("value");
    $suggestion->description = request()->get("description");
    $suggestion->save();

    $user = auth()->user();

    Mail::send('email.suporte', ["descricao" => request()->get("description"), "email" => $user->email, "title" => request()->get("title")], function($message)
    {
      $op = ["B" => "Bugson", "O" => "Outros", "S" => "Sugestão"];
      $message->to( "suporte@sysvale.com", "Suporte" )
              ->subject("LibreClass Suporte - " . $op[request()->get("value")]);
    });

    return Redirect::back()->with("success", "Obrigado pela sua mensagem. Nossa equipe irá analisar e responderá o mais breve possível.");
  }
}
