<?php

namespace Casa\Http\Controllers;
use Casa\User;
use Casa\Estado;
use Casa\Usuario;
use Casa\UsuarioNivel;
use Casa\Instituicao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Casa\Http\Requests\InstituicaoRequest;

class InstituicaoController extends Controller 
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() 
    {
        $instituicoes = Instituicao::where('is_aprovada', true)
        ->where('id', '<>', 1)
        ->orderBy('razao_social')
        ->paginate(config('app.list_size'));

        return view('instituicao.index', compact('instituicoes'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) 
    {
        $instituicao = Instituicao::findOrfail($id);
        
        $adm = User::where('instituicao_id', '=', $instituicao->id)
        ->whereIn('nivel_id', [UsuarioNivel::ADM_SISTEMA, UsuarioNivel::ADM_INSTITUICAO])->first();

        $estados = Estado::all()->pluck('UF', 'id');
        $disabled = true;

        return view('instituicao.edit', compact('instituicao', 'adm', 'estados', 'disabled'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) 
    {
        $instituicao = Instituicao::findOrfail($id);
        
        $adm = User::where('instituicao_id', '=', $instituicao->id)
        ->whereIn('nivel_id', [UsuarioNivel::ADM_SISTEMA, UsuarioNivel::ADM_INSTITUICAO])
        ->orderBy('name')
        ->first();

        $usuarios = User::where('instituicao_id', '=', $instituicao->id)
        ->where('deleted_at', null)
        ->pluck('name', 'id');

        $estados = Estado::all()->pluck('nome', 'id');
        $disabled = false;

        return view('instituicao.edit', compact('instituicao', 'adm', 'usuarios', 'estados', 'disabled'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(InstituicaoRequest $request, $id) 
    {
        $instituicao = Instituicao::findOrfail($id);

        $instituicao->atualizar($request->all());

        flash('Instituicao Alterada com Sucesso!', 'success');
        
        return redirect('instituicao');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) 
    {
        Instituicao::findOrFail($id)->delete();

        Usuario::where('instituicao_id', $id)->delete();

        flash("Instituição inativada com Sucesso", 'danger');
        return json_encode(['status' => true]);
    }

    public function buscar(Request $request) 
    {
        # Retirar os espaços do incios e fim da string.
        $request->inputBusca = trim($request->inputBusca);

        $instituicoes = Instituicao::where('is_aprovada', true)
        ->where('id', '<>', 1);

        if(!empty($request->inputBusca)) {
            $instituicoes = $instituicoes->where('razao_social', 'like', '%'.$request->inputBusca.'%')
            ->orWhere('cnpj','=', setMascara($request->inputBusca, '##.###.###/####-##'));
        } 

        $instituicoes = $instituicoes->orderBy('razao_social')->paginate(config('app.list_size'));

        $inputBusca = $request->inputBusca;

        return view('instituicao.index', compact('instituicoes', 'inputBusca'));
    }
}
