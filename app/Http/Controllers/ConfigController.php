<?php namespace App\Http\Controllers;

class ConfigController extends Controller
{

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
		$id = session("user");
		if ( $id == null || $id == "" )
			$this->user_id = false;
		else
			$this->user_id = decrypt($id);
	}

	public function getIndex()
	{
		if ( $this->user_id ) {
			return view("user.config", [ "user" => User::find($this->user_id), "select" => $this->select ]);
		}
		else {
			return redirect("/");
		}
	}

	public function postIndex()
	{
		return view("user.config", [ "user" => User::find($this->user_id), "select" => $this->select ]);
	}

	public function postPhoto()
	{
		if ( request()->hasFile("photo") && request()->file("photo")->isValid() )
		{
			$fileName = "/uploads/" . sha1($this->user_id) . "_" . microtime(true) . ".jpg";

			switch(request()->file("photo")->getMimeType())
			{
				case "image/png":
				case "image/jpeg":
				case "image/gif":
					break;
				default:
					return redirect("/config")->with("error", "Não pode ser modificado!");
			}

			$image    = new Imagick(request()->file("photo")->getRealPath());
			$width   = $image->getImageWidth();
			$height  = $image->getImageHeight();
			if ( $width < $height )
				$image->cropImage( $width, $width, 0, ($height-$width)/2);
			else
				$image->cropImage( $height, $height, ($width-$height)/2, 0);

			if ( $image->getImageHeight() > 400)
				$image->thumbnailImage(400, 400);

			//~ request()->file("imageproduct")->move("uploads", $fileName);
			$image->writeImage(__DIR__ . "/../../public" . $fileName);

			return User::whereId($this->user_id)->update(["photo" => $fileName ]) ?
														redirect("/config")->with("success", "Modificado com sucesso!") :
														redirect("/config")->with("error", "Não pode ser modificado!");
		}
		else
			return redirect("/config")->with("error", "Não pode ser modificado!");
	}

	public function postBirthdate()
	{
		$user = User::find($this->user_id);
		$user->birthdate = request()->get("birthdate-year") . "-" .
											 request()->get("birthdate-month") . "-" .
											 request()->get("birthdate-day");
		$user->save();
		return redirect("/config")->with("success", "Modificado com sucesso!"); //date("d / m / Y", strtotime($user->birthdate));
	}

	/**
	 * Atualiza os campos no formulário de cadastro
	 * @return type update
	 */
	public function postCommon()
	{
		foreach (request()->all() as $key => $value)
		{
			if ($key == "_token" || $key == "q") {
				continue;
			}
			User::whereId($this->user_id)->update([$key => $value]) ? $value: "error";
		}
//    return view("user.config", [ "user" => User::find($this->user_id), "select" => $this->select ]);
		return redirect("/config")->with("success", "Modificado com sucesso!");
	}

	public function postCommonselect()
	{
		foreach( request()->all() as $key => $value ) {
			if ( $key == "_token" || $key == "q") continue;

			return User::whereId($this->user_id)->update([$key => $value]) ?
								redirect("/config")->with("success", "Modificado com sucesso!"):
								redirect("/config")->with("erro", "Erro ao modificar!");
		}
	}

	public function postGender()
	{
		$user = User::find($this->user_id);
		$user->gender = request()->get("gender");
		$user->save();

		return redirect("/config")->with("success", "Modificado com sucesso!");
	}

	public function postType()
	{
		$user = User::find($this->user_id);
		$user->type = request()->get("type");
		$user->save();

		Session::put("type", $user->type);
		return redirect("/config")->with("success", "Modificado com sucesso!");
	}

	public function postPassword()
	{
		$user = User::find($this->user_id);
		if ( Hash::check(request()->get("password"), $user->password) )
		{
			$user->password = Hash::make(request()->get("newpassword"));
			$user->save();
			return redirect("/config")->with("success", "Modificado com sucesso!");
		}
		else
			return redirect("/config")->with("error", "Senha atual inválida!");

	}

	public function postLocation()
	{
		$city = City::whereName(request()->get("city"))->first();
		if ( $city == null ) {
			$state = State::whereShort(request()->get("state_short"))->first();
			if ( $state == null ) {
				$country = Country::whereShort(request()->get("country_short"))->first();
				if ( $country == null ) {
					$country = new Country;
					$country->name  = request()->get("country");
					$country->short = request()->get("country_short");
					$country->save();
				}
				$state = new State;
				$state->name = request()->get("state");
				$state->short = request()->get("state_short");
				$state->idCountry = $country->id;
				$state->save();
			}
			$city = new City;
			$city->name = request()->get("city");
			$city->idState = $state->id;
			$city->save();
		}

		$user = User::find($this->user_id);
		$user->idCity = $city->id;
		$user->save();

		return request()->get("city") . ", " . request()->get("state") . ", " . request()->get("country");
	}

	public function postStreet()
	{
		try {
			$user = User::find($this->user_id);
			$user->street = request()->get("street");
			$user->save();
			return redirect("/config")->with("success", "Modificado com sucesso!");
		}
		catch(Exception $e) {
			return redirect("/config")->with("error", "Erro ao inserir o endereço!");
		}
	}

	public function postUee()
	{
		try {
			$user = User::find($this->user_id);
			$user->uee = request()->get("uee");
			$user->save();
			return redirect("/config")->with("success", "Modificado com sucesso!");
		}
		catch(Exception $e){
			return redirect("/config")->with("error", "Erro ao inserir UEE!");
		}
	}

}
