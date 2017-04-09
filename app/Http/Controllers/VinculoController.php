<?php

namespace Casa\Http\Controllers;

use Casa\Adotivo;
use Casa\Adotante;
use Illuminate\Http\Request;
use Casa\Http\Requests\VinculoRequest;

class VinculoController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id) {
    	$adotivo = Adotivo::find($id);

    	$adotantesHistorico = $adotivo->adotantes()
    	->orderBy('adotantes_adotivos.created_at')
    	->where('adotantes_adotivos.adotivo_id', '=', $id)
    	->where('adotantes_adotivos.deleted_at', '!=', null)
    	->paginate(10); 
        # Caso o adotivo tenha vínculo trazer o id, senão null.
        $idAdotanteVinculo = $adotivo->adotantes()
        ->where('adotantes.has_vinculo','=', 1)
        ->first()['id'];

    	$adotantes = Adotante::orderBy('nome')->get();

    	foreach ($adotantes as $adotante) {
    		$adotante->nome = $adotante->getNomeEnomeConjuge(); 
    	}

    	$adotantes = $adotantes->pluck('nome', 'id');
   
		return view('vinculo.index', compact('adotivo', 'adotantesHistorico', 'adotantes', 'idAdotanteVinculo'));   
    }

    public function visualizar($adotivo_id, $adotante_id) {
    	
        $adotivo = Adotivo::find($adotivo_id);

         $adotantes = $adotivo->adotantes()
    	->where('adotantes_adotivos.adotante_id', '=', $adotante_id)
        ->first();

        return view('vinculo.visualizar', compact('adotivo', 'adotantes')); 
    	
    }

    public function vincular(VinculoRequest $request) {

        $adotivo = Adotivo::find($request->adotivo_id);
        $adotante = Adotante::find($request->adotante_id);

        $adotivo->status_id = 3;

        $adotante->has_vinculo = 1;

        
        $adotivo->adotantes()->save($adotante);
        $adotivo->save();
        return redirect('vinculos/adotivo/'.$adotivo->id);
    }

    public function desvincular(Request $request) {

        $adotivo = Adotivo::find($request->get('id_adotivo'));

        $adotante = $adotivo->adotantes()->where('adotantes.has_vinculo', '=', 1)->first();
        $adotivo->status_id = 2;
        $adotante->has_vinculo = 0;
        $adotante->save();

        $adotivo->adotantes()
        ->updateExistingPivot($adotante->id, ['observacoes' => $request->get('observacoes') , 'deleted_at' => date("Y-m-d G:i:s")]);
        
        $adotivo->save();

        return json_encode(['status' => true]);
    }
}
