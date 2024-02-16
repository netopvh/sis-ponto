<?php

namespace App\Http\Controllers;

use App\Models\Registro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Horario;
use DateTime;
date_default_timezone_set('America/Sao_Paulo');

class RegistroController extends Controller
{
    public function checaSeOFuncionarioEstaAdiantado($registroArray, $funcionario, $date) {
        $horario = $this->getFuncionarioHorario($funcionario[0]->id_horario);

        $horarioDoRegistro = $this->separarHorarioDaData($date);
        $horarioDoRegistro = strtotime($horarioDoRegistro);

        $ponto = $this->checaQualPonto($registroArray);
        switch ($ponto) {
            case 'primeiro_ponto':
                $horarioEntrada = strtotime($horario->primeiro_horario);
                $horarioEntrada = strtotime("-15 minutes", $horarioEntrada);

                if ($horarioDoRegistro < $horarioEntrada)
                    return "Volte 15 minutos antes do horário de entrada";
                break;
            case 'segundo_ponto':
               $horarioIdaIntervalo = strtotime($horario->segundo_horario);
                $horarioIdaIntervalo = strtotime("-15 minutes", $horarioIdaIntervalo);

                if ($horarioDoRegistro < $horarioIdaIntervalo)
                    return "Volte 15 minutos antes do horário de ida para intervalo";
                break;
            case 'terceiro_ponto':
                $horarioVoltaIntervalo = strtotime($horario->terceiro_horario);
                $horarioVoltaIntervalo = strtotime("-15 minutes", $horarioVoltaIntervalo);

                if ($horarioDoRegistro < $horarioVoltaIntervalo)
                    return "Volte 15 minutos antes do horário de volta para intervalo";
                break;
            case 'quarto_ponto':
                $horarioSaida = strtotime($horario->quarto_horario);
                $horarioSaida = strtotime("-15 minutes", $horarioSaida);

                if ($horarioDoRegistro < $horarioSaida)
                    return "Volte 15 minutos antes do horário de saida";
                break;
        }

        return null;
    }

    public function separarHorarioDaData($date) {
        $split = explode(' ', $date);
        $time = $split[1];
        return $time;
    }

    public function getFuncionarioHorario($horarioId) {
        $horario = Horario::where('id', $horarioId)->first();
        return $horario;
    }

    public function checaSeOFuncionarioEstaAtrasado($registroArray, $funcionario, $date)
    {
        $horario = $this->getFuncionarioHorario($funcionario[0]->id_horario);
        $ponto = $this->checaQualPonto($registroArray);

        $horarioDoRegistro = $this->separarHorarioDaData($date);
        $split = explode(':', $horarioDoRegistro);
        $registrationHour = intval($split[0]);
        $registrationMinute = intval($split[1]);

        switch ($ponto) {
            case 'primeiro_ponto':
                $split = explode(':', $horario->primeiro_horario);
                $horarioHour = intval($split[0]);
                $horarioMinute = intval($split[1]) + 15;

                if ($registrationHour > $horarioHour)
                    $registroArray['atrasou_primeiro_ponto'] = true;
                else if ($registrationHour == $horarioHour && $registrationMinute > $horarioMinute)
                    $registroArray['atrasou_primeiro_ponto'] = true;

                return $registroArray;
                break;
            case 'segundo_ponto':
                $split = explode(':', $horario->segundo_horario);
                $horarioHour = intval($split[0]);
                $horarioMinute = intval($split[1]) + 15;

                if ($registrationHour > $horarioHour)
                    $registroArray['atrasou_segundo_ponto'] = true;
                else if ($registrationHour == $horarioHour
                    && $registrationMinute > $horarioMinute)
                    $registroArray['atrasou_segundo_ponto'] = true;

                return $registroArray;
                break;
            case 'terceiro_ponto':
                $split = explode(':', $horario->terceiro_horario);
                $horarioHour = intval($split[0]);
                $horarioMinute = intval($split[1]) + 15;

                if ($registrationHour > $horarioHour)
                    $registroArray['atrasou_terceiro_ponto'] = true;
                else if ($registrationHour == $horarioHour
                    && $registrationMinute > $horarioMinute)
                    $registroArray['atrasou_terceiro_ponto'] = true;

                return $registroArray;
                break;
            case 'quarto_ponto':
                $split = explode(':', $horario->quarto_horario);
                $horarioHour = intval($split[0]);
                $horarioMinute = intval($split[1]) + 15;

                if ($registrationHour > $horarioHour)
                    $registroArray['atrasou_quarto_ponto'] = true;
                else if ($registrationHour == $horarioHour
                    && $registrationMinute > $horarioMinute)
                    $registroArray['atrasou_quarto_ponto'] = true;

                return $registroArray;
                break;
        }
        return $registroArray;
    }

    public function batePonto($registroFuncionario, $ponto, $date)
    {

        if ($ponto == 'segundo_ponto') {
            Db::table('registros')
                ->where('id', $registroFuncionario->id)
                ->update([$ponto => $date,
                    'atrasou_segundo_ponto' => $registroFuncionario->atrasou_segundo_ponto,
                        ]);
        } else if ($ponto == 'terceiro_ponto') {
            Db::table('registros')
                ->where('id', $registroFuncionario->id)
                ->update([$ponto => $date,
                    'atrasou_terceiro_ponto' => $registroFuncionario->atrasou_terceiro_ponto,
                        ]);
        } else if ($ponto == 'quarto_ponto') {
            Db::table('registros')
                ->where('id', $registroFuncionario->id)
                ->update([$ponto => $date,
                    'atrasou_quarto_ponto' => $registroFuncionario->atrasou_quarto_ponto,
                        ]);
        }
        $registro = Registro::latest()->first();

        return $registro;
    }

    public function checaQualPonto($registroFuncionario)
    {
        // tirei o toArray() daqui
        if (strcmp(gettype($registroFuncionario), 'object') == 0)
            $registroArray = $registroFuncionario->toArray();
        else
            $registroArray = $registroFuncionario;

        foreach ($registroArray as $key => $value) {
            if ($key == 'primeiro_ponto' && $value == null) {
                return $key;
            }
            if ($key == 'segundo_ponto' && $value == null) {
                return $key;
            }
            if ($key == 'terceiro_ponto' && $value == null) {
                return $key;
            }
            if ($key == 'quarto_ponto' && $value == null) {
                return $key;
            }
        }
        return null;
    }

    public function checaSeORegistroFoiCriadoNoMesmoDia($registroDate)
    {
        $date = date('Y-m-d');
        $rest = strpos($registroDate, $date);
        if ($rest !== false) {
            $split = explode('T', $registroDate);
            $split = explode(' ', $registroDate);
            $registroDate = $split[0];
            if ($registroDate == $date) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getUltimoRegistroDoFuncionario($funcionarioId)
    {
        $registro = Registro::where('id_funcionario', $funcionarioId)->latest()->get()->first();

        return $registro;
    }
    /**
     * Display a listing of the resource.
     */
    public function createRegistroArray($funcionario)
    {
        $registroArray = array(
            'id_funcionario' => $funcionario[0]->id,
            'id_horario' => $funcionario[0]->id_horario,
            'primeiro_ponto' => null,
            'segundo_ponto' => null,
            'terceiro_ponto' => null,
            'quarto_ponto' => null,
            'atrasou_primeiro_ponto' => false,
            'atrasou_segundo_ponto' => false,
            'atrasou_terceiro_ponto' => false,
            'atrasou_quarto_ponto' => false
        );

        return $registroArray;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($registroJson)
    {
        $id = Registro::create($registroJson);
        return $id;
    }

    public function batePrimeiroPonto($registroArray, $funcionario, $date) {
        $estaAdiantado = $this->checaSeOFuncionarioEstaAdiantado($registroArray, $funcionario, $date);
        if ($estaAdiantado != null)
            return $estaAdiantado;
        $registroArray['primeiro_ponto'] = $date;
        $novoRegistro = $this->create($registroArray);
        return $novoRegistro;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($funcionario)
    {
        $data = date('Y-m-d H:i:s');

        $registroFuncionario = $this->getUltimoRegistroDoFuncionario($funcionario[0]->id);
        $registroArray = $this->createRegistroArray($funcionario);

        if ($registroFuncionario == null) {
            $registroArray = $this->checaSeOFuncionarioEstaAtrasado($registroArray, $funcionario, $data);
            $novoRegistro = $this->batePrimeiroPonto($registroArray, $funcionario, $data);
            return $novoRegistro;
        } else {
            if ($this->checaSeORegistroFoiCriadoNoMesmoDia($registroFuncionario->created_at) !== false) {
                $registroFuncionario = $this->checaSeOFuncionarioEstaAtrasado($registroFuncionario, $funcionario, $data);
                $ponto = $this->checaQualPonto($registroFuncionario);

                if (!$ponto) {
                    $registroArray = $this->checaSeOFuncionarioEstaAtrasado($registroArray, $funcionario, $data);
                    return $this->batePrimeiroPonto($registroArray, $funcionario, $data);
                } else {
                    $estaAdiantado = $this->checaSeOFuncionarioEstaAdiantado($registroFuncionario, $funcionario, $data);
                    if ($estaAdiantado != null)
                        return $estaAdiantado;
                    $novoRegistro = $this->batePonto($registroFuncionario, $ponto, $data);
                    return $novoRegistro;
                }
            } else {
                $registroArray = $this->checaSeOFuncionarioEstaAtrasado($registroArray, $funcionario, $data);
                return $this->batePrimeiroPonto($registroArray, $funcionario, $data);
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function retornaTodoORegistroDoFuncionario(Request $request)
    {
        // transforma a data para o formato correto
        $registro = Registro::where('id_funcionario', $request->id_funcionario)->orderBy('created_at', 'desc')->get();
        for ($i = 0; $i < count($registro); $i++) {
            if ($registro[$i]['primeiro_ponto'] != null)
                $registro[$i]['primeiro_ponto'] = date("d/m/Y H:i:s", strtotime($registro[$i]['primeiro_ponto']));
            if ($registro[$i]['segundo_ponto'] != null)
                $registro[$i]['segundo_ponto'] = date("d/m/Y H:i:s", strtotime($registro[$i]['segundo_ponto']));
            if ($registro[$i]['terceiro_ponto'] != null)
                $registro[$i]['terceiro_ponto'] = date("d/m/Y H:i:s", strtotime($registro[$i]['terceiro_ponto']));
            if ($registro[$i]['quarto_ponto'] != null)
                $registro[$i]['quarto_ponto'] = date("d/m/Y H:i:s", strtotime($registro[$i]['quarto_ponto']));
        }
        return $registro;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Registro $registro)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Registro $registro)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Registro $registro)
    {
        //
    }
}
