<?php namespace App\Http\Controllers;

class ProgressionController extends Controller
{
  public function __construct()
  {

  }

	public function postStudentsAndClasses()
	{
		if(!request()->has('previous_classe_id')) {
			return ['status' => 0, 'message' => 'Nenhuma turma selecionada.'];
		}

		$previous_classe_id = decrypt(request()->get('previous_classe_id'));
		$classe_id = decrypt(request()->get('classe_id'));
		// $atual_classe = Classe::find($previous_classe_id);
		// $atual_period = Period::find($atual_classe->period_id);

		// //Se não há configuração de progressão da série atual
		// if(empty($atual_period->progression_value)) {
		// 	return ['status' => 0, 'message' => 'Não foi possível obter as turmas para progressão. Verifique a sequência de progressão em <a href="periods">Meus Períodos</a>.'];
		// }

		// $next_period = Period::where('course_id', $atual_period->course_id)->where('progression_value', $atual_period->progression_value + 1)->first();
    //
		// //Se não há configuração de progressão da próxima série
		// if(empty($next_period)) {
		// 	return ['status' => 0, 'message' => 'Não existe progressão configurada para a série. Verifique a sequência de progressão em <a href="periods">Meus Períodos</a>.'];
		// }
    //
		// $next_period->classes = Classe::where('period_id', $next_period->id)->where('school_year', $atual_classe->school_year + 1)->get();

		//Se não há configuração de progressão da próxima série
		// if(empty($next_period->classes)) {
		// 	return ['status' => 0, 'message' => 'Não existem turmas criadas para o próximo ano escolar.'];
		// }

		//Obtém alunos matriculados na turma atual
		$students = DB::select("
			SELECT Users.id
			FROM Classes, Offers, Units, Attends, Users
			WHERE Classes.id = Offers.class_id
				AND Offers.id = Units.offer_id
				AND Units.id = Attends.unit_id
				AND Users.id = Attends.user_id
				AND Classes.id = $classe_id
				AND Attends.status = 'M'
			GROUP BY Users.id
			ORDER BY Users.name
		");

		$students = implode(', ', array_pluck($students, 'id'));



		//Obtém alunos matriculados na turma anterior
		$attends = DB::select("
			SELECT Users.name as user_name, Attends.id as attend_id, Users.id as user_id
			FROM Classes, Offers, Units, Attends, Users
			WHERE Classes.id = Offers.class_id
				AND Offers.id = Units.offer_id
				AND Units.id = Attends.unit_id ".
				(!empty($students) ? "AND Users.id NOT IN ($students) " : "").
				"AND Users.id = Attends.user_id
				AND Classes.id = $previous_classe_id
				AND Attends.status = 'M'
			GROUP BY Users.id
			ORDER BY Users.name
		");

		foreach($attends as $attend) {
			$attend->user_id = encrypt($attend->user_id);
			$attend->attend_id = encrypt($attend->attend_id);
		}

		// return ['status' => 1, 'attends' => $attends, 'atual_classe' => $atual_classe, 'atual_period' => $atual_period, 'next_classe' => $next_period];
		return ['status' => 1, 'attends' => $attends ];
	}

	public function postImportStudent() {
		if(!count(request()->get('student_ids'))) {
			return ['status' => 0, 'message' => 'Nenhum aluno selecionado'];
		}

		$classe_id = request()->get('classe_id');
		$offers = Offer::where('class_id', decrypt($classe_id))->get();

		if(!$offers) {
			return ['status' => 0, 'message' => 'A turma ainda não possui ofertas'];
		}

		$students_ids = request()->get('student_ids');
		foreach($offers as $offer) {
			if(!count($offer->units)) {
				return ['status' => 0, 'message' => 'Não foi possível importar. Existem ofertas sem unidades.'];
			}
		}

		foreach($offers as $offer) {
			foreach($offer->units as $unit) {
				foreach($students_ids as $student_id) {
					Attend::create(['user_id' => decrypt($student_id), 'unit_id' => $unit->id]);
				}
			}
		}

		return ['status' => 1];

	}
}
