<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\HorarioController;
use Illuminate\Support\Facades\Auth;
use Hash;


class CadastroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $horario = new HorarioController();
        $horarioJson = $horario->setHorario($input['horario']);
        $horarioId = $horario->create($horarioJson);

        $funcionario = new FuncionarioController();
        $input['id_horario'] = $horarioId->id;
        $input = json_encode($input);
        $funcionarioId = $funcionario->create($input);

        // return $horarioJson;
        // return $funcionario;
        // print_r($dataJson);
        return response()->json(['message' => 'Funcionário criado com sucesso!', 'funcionarioId' => $funcionarioId->id]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
