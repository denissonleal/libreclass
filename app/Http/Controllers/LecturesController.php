<?php namespace App\Http\Controllers;

class LecturesController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth.type:P');
	}

	public function getIndex()
	{
		$user = auth()->user();
		$lectures = Lecture::where("user_id", auth()->id())->orderBy("order")->get();
		$lectures = array_where($lectures, function($key, $value) {
			return $value->offer->classe->status != 'F';
		});

		return view("offers.teacher", ["user" => $user, "lectures" => $lectures]);
	}

	public function getFinalreport($offer = "")
	{
		if (auth()->id()) {
			$user = auth()->user();
		}
		$offer = Offer::find(decrypt($offer));
		$course = $offer->getDiscipline()->getPeriod()->getCourse();
		$qtdLessons = $offer->qtdLessons();

		$lessons = $offer->lessons();

		$alunos = DB::select("SELECT Users.id, Users.name
			FROM Attends, Units, Users
			WHERE Units.offer_id=? AND Units.id=Attends.unit_id AND Attends.user_id=Users.id
			AND Attends.status = 'M'
			GROUP BY Attends.user_id
			ORDER BY Users.name", [$offer->id]);

		$units = Unit::where("offer_id", $offer->id)->get();

		foreach ($alunos as $aluno) {
			$aluno->absence = $offer->qtdAbsences($aluno->id);

			//Obtém os atestados e quantidade
			$attests = Attest::where('student_id', $aluno->id)->get();
			$qtdAttests = 0;
			foreach($lessons as $lesson) {
				foreach($attests as $attest) {
					$attest->dateFinish = date('Y-m-d', strtotime($attest->date. '+ '. ($attest->days - 1) .' days'));
					//If true, aluno possui um atestado para o dia da aula.
					if (($lesson->date >= $attest->date) && ($lesson->date <= $attest->dateFinish))
					{
						$qtdAttests++;
					}
				}
			}
			$aluno->absence -= $qtdAttests;


			$aluno->averages = [];
			$sum = 0.;
			foreach ($units as $unit) {
				$exam = $unit->getAverage($aluno->id);

				if ($exam[1] !== null) {
					$aluno->averages[$unit->value] = $exam[0] < $course->average ? $exam[1] : $exam[0];
				} else {
					$aluno->averages[$unit->value] = $exam[0];
				}

				$sum += $aluno->averages[$unit->value];
			}
			$aluno->med = $sum / count($units);

			if ($aluno->med >= $course->average) {
				$aluno->rec = "-";
				$aluno->result = "Ap. por nota";
				$aluno->status = "label-success";
			} else {
				$rec = FinalExam::where("offer_id", $offer->id)->where("user_id", $aluno->id)->first();
				$aluno->rec = $rec ? $rec->value : "0.00";
				if ($aluno->rec >= $course->average_final) {
					$aluno->result = "Aprovado";
					$aluno->status = "label-success";
				} else {
					$aluno->result = "Rep. por nota";
					$aluno->status = "label-danger";
				}
			}
			$qtdLessons = $qtdLessons ? $qtdLessons : 1; /* evitar divisão por zero */
			if ($aluno->absence / $qtdLessons * 100. > $course->absent_percent) {
				$aluno->result = "Rep. por falta";
				$aluno->status = "label-danger";
			}
		}

		return view("modules.disciplines.finalreport", [
			"user" => $user,
			"units" => $units,
			"students" => $alunos,
			"offer" => $offer,
			"qtdLessons" => $qtdLessons,
			"course" => $course,
		]);
	}

	public function getFrequency($offer)
	{
		$user = auth()->user();
		$offer = Offer::find(decrypt($offer));
		if ($offer->getLectures()->user_id != auth()->id()) {
			return redirect("/lectures")->with("error", "Você não tem acesso a essa página");
		}
		$units = Unit::where("offer_id", $offer->id)->get();
		$students = DB::select("select Users.id, Users.name "
			. "from Users, Attends, Units "
			. "where Units.offer_id=? and Attends.unit_id=Units.id and Attends.user_id=Users.id and Attends.status = 'M'"
			. "group by Users.id order by Users.name", [$offer->id]);

		return view("modules.frequency", ["user" => $user, "offer" => $offer, "units" => $units, "students" => $students]);
		return $offer;
	}

	public function postSort()
	{
		foreach (request()->get("order") as $key => $value) {
			Lecture::where('offer_id', decrypt($value))->where('user_id', auth()->id())->update(["order" => $key + 1]);
		}
	}
}
