<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Compra;
use App\Produto;
use App\User;

class CompraController extends BaseController
{
    static public function store($items, $user_id, $codigo)
    {
        $compra = new Compra();
        $compra->codigo = $codigo;
        $compra->preco = 0;

        $user = User::find($user_id);
        $compra->user()->associate($user)->save();

        $preco = 0;
        foreach ($items as $item) {
            $produto = Produto::find($item['id']);
            $preco += $produto->preco * $item['quantidade'];
            $compra->produtos()->attach($produto, ['quantidade' => $item['quantidade']]);
        }
        $compra->preco = $preco;
        $compra->save();
    }

    static public function update($codigo, $autorizacao, $status)
    {
        $compra = Compra::all()->where('codigo', $codigo)->last();
        if (!$compra)
            return false;

        $compra->status = $status;
        $compra->autorizacao = $autorizacao;
        $compra->save();

        return true;
    }

    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'id' => 'required|integer'
        ]);

        if ($validator->fails())
            return $this->enviarRespostaErro('Erros de validação.', $validator->errors());

        $user = User::find($request->user_id);
        if (!$user)
            return $this->enviarRespostaErro('Usuário inválido');

        $compra = Compra::find($request->id);
        if (!$compra)
            return $this->enviarRespostaErro('Compra inválida');

        if ($compra->user->id != $user->id)
            return $this->enviarRespostaErro('Id não correspondente');

        //remove usuário do json q vai ser retornado
        $compra = collect($compra);
        $compra->pop();

        return $this->enviarRespostaSucesso($compra, 'Compra mostrada com sucesso');
    }

    public function userIndex(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer'
        ]);

        if ($validator->fails())
            return $this->enviarRespostaErro('Erros de validação.', $validator->errors());

        $user = User::find($request->user_id);
        if (!$user)
            return $this->enviarRespostaErro('Usuário inválido');

        $compras = $user->compras;
        return $this->enviarRespostaSucesso($compras, 'Compras mostradas com sucesso');
    }

    public function index()
    {
        $compras = Compra::all();
        return $this->enviarRespostaSucesso($compras, 'Compras mostradas com sucesso');
    }
}
