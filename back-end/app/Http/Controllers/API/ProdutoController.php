<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Produto;
use App\Http\Controllers\API\BaseController;
use Illuminate\Support\Facades\Storage;

class ProdutoController extends BaseController
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'descricao' => 'required|string',
            'preco' => 'required|numeric',
            'imagem' => 'required|file',
            'peso' => 'numeric', // opcional, peso do item para calculo do frete
            // caso necessário filtrar métodos de pagamento, juros, descontos receber os parâmetros aqui
        ]);

        if ($validator->fails())
            return $this->enviarRespostaErro('Erros de validação.', $validator->errors());

        $imagem = Storage::putFile('public/imagens', $request->file('imagem'), 'public');
        $imagem = substr($imagem, 7);

        $produto = new Produto;
        $produto->descricao = $request->descricao;
        $produto->preco = floatval($request->preco);
        $produto->imagem = $imagem;
        if ($request->peso)
            $produto->peso = floatval($request->peso);
        $produto->save();

        return $this->enviarRespostaSucesso($produto, 'Produto registrado com sucesso', 201);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'descricao' => 'required|string',
            'preco' => 'required|numeric',
            'imagem' => 'file',
            'peso' => 'numeric', // opcional, peso do item para calculo do frete
            // caso necessário filtrar métodos de pagamento, juros, descontos receber os parâmetros aqui
        ]);

        if ($validator->fails())
            return $this->enviarRespostaErro('Erros de validação.', $validator->errors());

        $produto = Produto::find($request->id);
        if (!$produto)
            return $this->enviarRespostaErro('Produto inválido');

        if($request->has('imagem'))
        {
            Storage::delete('public/'.$produto->imagem);
            $imagem = Storage::putFile('public/imagens', $request->file('imagem'), 'public');
            $imagem = substr($imagem, 7);
            $produto->imagem = $imagem;
        }
        
        $produto->descricao = $request->descricao;
        $produto->preco = floatval($request->preco);
        if ($request->peso)
            $produto->peso = floatval($request->peso);
        $produto->save();

        return $this->enviarRespostaSucesso($produto, 'Produto atualizado com sucesso', 200);
    }

    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails())
            return $this->enviarRespostaErro('Erros de validação.', $validator->errors());

        $produto = Produto::find($request->id);
        if (!$produto)
            return $this->enviarRespostaErro('Produto inválido');

        Storage::delete('public/'.$produto->imagem);
        $produto->delete();

        return $this->enviarRespostaSucesso(null, 'Produto excluido com sucesso', 200);
    }


    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails())
            return $this->enviarRespostaErro('Erros de validação.', $validator->errors());

        $produto = Produto::find($request->id);
        if (!$produto)
            return $this->enviarRespostaErro('Produto inválido');

        return $this->enviarRespostaSucesso($produto, 'Produto mostrado com sucesso', 200);
    }

    public function index()
    {  
        $produtos = Produto::all();
        return $this->enviarRespostaSucesso($produtos, 'Produto registrados', 200);
    }
}
