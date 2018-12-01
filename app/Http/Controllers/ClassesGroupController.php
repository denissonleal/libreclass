<?php

class ClassesGroupController extends \BaseController
{

  private $user_id;

  public function __construct()
  {
    $id = Session::get("user");
    if ($id == null || $id == "") {
      $this->user_id = false;
    } else {
      $this->user_id = decrypt($id);
    }
  }

  /**
   * Carrega view para agrupar ofertas.
   * @param  [String] $idClass [Id da turma].
   * @return [View]
   */
  public function loadClassGroup($idClass)
  {
    $classe = Classe::find(decrypt($idClass));
    $classe->disciplines = $this->getOffers($idClass);
    $classe->id = encrypt($classe->id);

    $user = User::find($this->user_id);
    return View::make("modules.classesGroup", ['user' => $user, 'classe' => $classe]);
  }

  /**
   * Obtém nomes e ids das ofertas associadas a uma turma
   * @param  [string] $idClass [Id criptografado da turma]
   * @return [array]           [Ofertas]
   */
  private function getOffers($idClass)
  {
    $o = [];
    $offers = Offer::where('idClass', decrypt($idClass))->get();
    foreach ($offers as $offer) {
      if ($offer->discipline) {
        $o[] = (object) [
          'name' => $offer->discipline->name,
          'id' => encrypt($offer->id),
          'grouping' => $offer->grouping,
          'master_discipline' => ($offer->grouping == 'S') ? Offer::find($offer->idOffer)->discipline->name : null,
        ];
      }
    }
    return $o;
  }

  /**
   * Retorna nomes e ids das ofertas associadas a uma turma em formato JSON
   * @param  [string] $idClass [Id criptografado da turma]
   * @return [json]            [Ofertas]
   */
  public function jsonOffers()
  {
    try {
      return Response::json(['status' => 1, 'disciplines' => $this->getOffers(Input::get('idClass'))]);
    } catch (Exception $e) {
      return Response::json(['status' => 0, 'message' => 'Erro: ' . $e->getMessage() . ' (' . $e->getLine() . ')']);
    }
  }

  /**
   * Cria uma oferta 'Master', aquela que contém ofertas agrupadas.
   * @return [json] [Retorna status e, se necessário, uma mensagem de erro]
   */
  public function createMasterOffer()
  {
    try {
      if (!Input::has('offers') || !Input::has('classe') || !Input::has('name')) {
        throw new Exception('Informações incompletas.');
      }
      $master_discipline = Discipline::create(['name' => Input::get('name')]);
      $master_offer = Offer::create([
        'idClass' => decrypt(Input::get('classe')),
        'idDiscipline' => $master_discipline->id,
        'grouping' => 'M',
      ]);
      $unit = new Unit;
      $unit->IdOffer = $master_offer->id;
      $unit->value = "1";
      $unit->calculation = "A";
      $unit->save();
      foreach (Input::get('offers') as $offer_id) {
        $id = decrypt($offer_id);
        $offer = Offer::find($id);
        $offer->idOffer = $master_offer->id;
        $offer->grouping = 'S';
        $offer->save();
      }
      return Response::json(['status' => 1, 'message' => 'Disciplinas agrupadas com sucesso!']);
    } catch (Exception $e) {
      return Response::json(['status' => 0, 'message' => 'Erro: ' . $e->getMessage() . ' (' . $e->getLine() . ')']);
    }
  }

}
