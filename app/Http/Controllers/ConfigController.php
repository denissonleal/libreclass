<?php

class ConfigController extends \BaseController {

	/**
	 *
	 * @var type
	 */
	private $user_id;

	private $select = ["gender" => [ "M" => "Masculino", "F" => "Feminino"],
										 "formation" => [ "Não quero informar",
																		"Ensino Fundamental",
																		"Ensino Médio",
																		"Ensino Superior Incompleto",
																		"Ensino Superior Completo",
																		"Pós-Graduado",
																		"Mestre",
																		"Doutor"],
										 "type" => ["P" => "Professor", "A" => "Aluno", "T" => "Professor/Aluno", "I" => "Instituição"],
										];

	/**
	 *
	 */
	public function ConfigController()
	{
		$id = Session::get("user");
		if ( $id == null || $id == "" )
			$this->user_id = false;
		else
			$this->user_id = decrypt($id);
	}

	public function getIndex()
	{
		if ( $this->user_id ) {
			return View::make("user.config", [ "user" => User::find($this->user_id), "select" => $this->select ]);
		}
		else {
			return Redirect::guest("/");
		}
	}

	public function postIndex()
	{
		return View::make("user.config", [ "user" => User::find($this->user_id), "select" => $this->select ]);
	}

	public function postPhoto()
	{
		if ( Input::hasFile("photo") && Input::file("photo")->isValid() )
		{
			$fileName = "/uploads/" . sha1($this->user_id) . "_" . microtime(true) . ".jpg";

			switch(Input::file("photo")->getMimeType())
			{
				case "image/png":
				case "image/jpeg":
				case "image/gif":
					break;
				default:
					return Redirect::to("/config")->with("error", "Não pode ser modificado!");
			}

			$image    = new Imagick(Input::file("photo")->getRealPath());
			$width   = $image->getImageWidth();
			$height  = $image->getImageHeight();
			if ( $width < $height )
				$image->cropImage( $width, $width, 0, ($height-$width)/2);
			else
				$image->cropImage( $height, $height, ($width-$height)/2, 0);

			if ( $image->getImageHeight() > 400)
				$image->thumbnailImage(400, 400);

			//~ Input::file("imageproduct")->move("uploads", $fileName);
			$image->writeImage(__DIR__ . "/../../public" . $fileName);

			return User::whereId($this->user_id)->update(["photo" => $fileName ]) ?
														Redirect::to("/config")->with("success", "Modificado com sucesso!") :
														Redirect::to("/config")->with("error", "Não pode ser modificado!");
		}
		else
			return Redirect::to("/config")->with("error", "Não pode ser modificado!");
	}

	public function postBirthdate()
	{
		$user = User::find($this->user_id);
		$user->birthdate = Input::get("birthdate-year") . "-" .
											 Input::get("birthdate-month") . "-" .
											 Input::get("birthdate-day");
		$user->save();
		return Redirect::to("/config")->with("success", "Modificado com sucesso!"); //date("d / m / Y", strtotime($user->birthdate));
	}

	/**
	 * Atualiza os campos no formulário de cadastro
	 * @return type update
	 */
	public function postCommon()
	{
		foreach (Input::all() as $key => $value)
		{
			if ($key == "_token" || $key == "q") {
				continue;
			}
			User::whereId($this->user_id)->update([$key => $value]) ? $value: "error";
		}
//    return View::make("user.config", [ "user" => User::find($this->user_id), "select" => $this->select ]);
		return Redirect::to("/config")->with("success", "Modificado com sucesso!");
	}

	public function postCommonselect()
	{
		foreach( Input::all() as $key => $value ) {
			if ( $key == "_token" || $key == "q") continue;

			return User::whereId($this->user_id)->update([$key => $value]) ?
								Redirect::to("/config")->with("success", "Modificado com sucesso!"):
								Redirect::to("/config")->with("erro", "Erro ao modificar!");
		}
	}

	public function postGender()
	{
		$user = User::find($this->user_id);
		$user->gender = Input::get("gender");
		$user->save();

		return Redirect::to("/config")->with("success", "Modificado com sucesso!");
	}

	public function postType()
	{
		$user = User::find($this->user_id);
		$user->type = Input::get("type");
		$user->save();

		Session::put("type", $user->type);
		return Redirect::to("/config")->with("success", "Modificado com sucesso!");
	}

	public function postPassword()
	{
		$user = User::find($this->user_id);
		if ( Hash::check(Input::get("password"), $user->password) )
		{
			$user->password = Hash::make(Input::get("newpassword"));
			$user->save();
			return Redirect::to("/config")->with("success", "Modificado com sucesso!");
		}
		else
			return Redirect::to("/config")->with("error", "Senha atual inválida!");

	}

	public function postLocation()
	{
		$city = City::whereName(Input::get("city"))->first();
		if ( $city == null ) {
			$state = State::whereShort(Input::get("state_short"))->first();
			if ( $state == null ) {
				$country = Country::whereShort(Input::get("country_short"))->first();
				if ( $country == null ) {
					$country = new Country;
					$country->name  = Input::get("country");
					$country->short = Input::get("country_short");
					$country->save();
				}
				$state = new State;
				$state->name = Input::get("state");
				$state->short = Input::get("state_short");
				$state->idCountry = $country->id;
				$state->save();
			}
			$city = new City;
			$city->name = Input::get("city");
			$city->idState = $state->id;
			$city->save();
		}

		$user = User::find($this->user_id);
		$user->idCity = $city->id;
		$user->save();

		return Input::get("city") . ", " . Input::get("state") . ", " . Input::get("country");
	}

	public function postStreet()
	{
		try {
			$user = User::find($this->user_id);
			$user->street = Input::get("street");
			$user->save();
			return Redirect::to("/config")->with("success", "Modificado com sucesso!");
		}
		catch(Exception $e) {
			return Redirect::to("/config")->with("error", "Erro ao inserir o endereço!");
		}
	}

	public function postUee()
	{
		try {
			$user = User::find($this->user_id);
			$user->uee = Input::get("uee");
			$user->save();
			return Redirect::to("/config")->with("success", "Modificado com sucesso!");
		}
		catch(Exception $e){
			return Redirect::to("/config")->with("error", "Erro ao inserir UEE!");
		}
	}

}
