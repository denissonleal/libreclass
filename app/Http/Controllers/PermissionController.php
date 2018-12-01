<?php

class PermissionController extends \BaseController {

	public function __construct()
  {
    $id = Session::get("user");
    if ( $id == null || $id == "" )
      $this->user_id = false;
    else
      $this->user_id = decrypt($id);
  }

	public function getIndex() {
		if ( Session::has("redirect") )
			return redirect(Session::get("redirect"));

		$friends = DB::select("SELECT Users.id, Users.name, Users.enrollment FROM Relationships, Users WHERE Relationships.user_id=? AND Relationships.idFriend=Users.id", [$this->user_id]);

		$listfriends = [];
		$keys = [];
		foreach($friends as $friend) {
			$keys[$friend->id] = encrypt($friend->id);
			$listfriends[$keys[$friend->id]] = $friend->name;
		}

		$ctrls = Ctrl::where("user_id", $this->user_id)->get();
		$listmodules = [];
		foreach($ctrls as $ctrl)
			$listmodules[$ctrl->idModule] = Module::find($ctrl->idModule)->name;

		$user = User::find($this->user_id);
		Session::put("type", $user->type);

		$adminers = DB::select("SELECT Users.id, Users.name, Users.email, Users.enrollment "
									."FROM Controllers, Adminers, Users "
									."WHERE Controllers.user_id=? AND Controllers.id=Adminers.idController AND Adminers.user_id=Users.id "
									."GROUP BY Users.id", [$this->user_id]);

		foreach($adminers as $adminer )
		{
			if( !isset($keys[$adminer->id]) )
				$keys[$adminer->id] = encrypt($adminer->id);

			$adminer->modules = DB::select("SELECT Modules.id, Modules.name "
											."FROM Controllers, Adminers, Modules "
											."WHERE Controllers.user_id=? AND Controllers.idModule=Modules.id AND Controllers.id=Adminers.idController AND Adminers.user_id=? ", [$this->user_id, $adminer->id]);
			$adminer->id = $keys[$adminer->id];
		}


		return view("institution.permissions", ["user" => $user, "listfriends" => $listfriends, "listmodules" => $listmodules, "adminers" => $adminers]);
	}

	public function postIndex() {
		// return Input::all();
		$user = decrypt(Input::get("id"));

		$ctrls = Ctrl::where("user_id", $this->user_id)->get();
		foreach( $ctrls as $ctrl )
			Adminer::where("user_id", $user)->where("idController", $ctrl->id)->delete();

		if (Input::has("ctrl"))
			foreach( Input::get("ctrl") as $ctrl) {
				$adminer = new Adminer;
				$adminer->user_id = $user;
				$adminer->idController = $ctrl;
				$adminer->save();
			}

		return redirect("/permissions")->with("success", "Modificado com sucesso!");
	}

	public function postFind()
	{
		$user = User::find(decrypt(Input::get("id")));

		$modules = DB::select("SELECT Modules.id, Modules.name "
										."FROM Controllers, Adminers, Modules "
										."WHERE Controllers.user_id=? AND Controllers.idModule=Modules.id AND Controllers.id=Adminers.idController AND Adminers.user_id=? ", [$this->user_id, $user->id]);
		$usermodules = [];
		foreach($modules as $module)
			$usermodules[] = $module->id;

		$user->modules = $usermodules;
		unset($user->id);
		unset($user->password);
		unset($user->created_at);
		unset($user->updated_at);
		unset($user->birthdate);
		unset($user->institution);
		unset($user->course);
		unset($user->type);
		unset($user->gender);
		unset($user->photo);
		unset($user->idCity);
		unset($user->formation);

		return $user;
	}
}
