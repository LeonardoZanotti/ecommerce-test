<?php

// 857.443.150-80

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Controllers\API\CompraController as CompraController;
use Illuminate\Support\Facades\Validator;
use App\User;
use App\Produto;
use App\Compra;
use Picpay\Payment;
use Picpay\Buyer;
use Picpay\Seller;
use Picpay\Request\NotificationRemoteRequest;
use Picpay\Request\PaymentRequest;
use Picpay\Request\StatusRequest;
use Picpay\Request\CancelRequest;
use Picpay\Exception\RequestException;

class PicPayController extends BaseController
{
    public function checkout(Request $request) {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.quantidade' => 'required|integer'
            // {{"item": "item1", "quantidade":"2"}, {"item": "item2", "quantidade": "2"}}
        ]);        
        
        if ($validator->fails()) {
            return $this::enviarRespostaErro('Erros de validação', $validator->errors());
        }

        $user = $request->user();
        
        $preco = 0;
        foreach ($request->items as $item) {
            $id = $item['id'];
            
            $produto = Produto::find($id);
            if (!$produto) {
                return $this::enviarRespostaErro('Produto inexistente', "id: $id");
            }

            $preco += $produto->preco * $item['quantidade'];
        }
        
        $codigo = $this->gerarCodigo();
        $compra = CompraController::store($request->items, $user->id, $codigo);

        $seller = new Seller(env('PICPAY_TOKEN'), env('PICPAY_SELLER_TOKEN'));
        $buyer = new Buyer($user->name, $user->lastName, $user->cpf, $user->email, $user->telefone);
        $payment = new Payment($codigo, env('CALLBACK_URL'), $preco, $buyer, env('RETURN_URL'));

        try {
            $paymentRequest = new PaymentRequest($seller, $payment);

            $paymentResponse = $paymentRequest->execute();

            return $this::enviarRespostaSucesso($paymentResponse, 'Pagamento gerado com sucesso!', 201);
        } catch (RequestException $e) {
            return $this::enviarRespostaErro($e->getErrors(), $e->getMessage(), $e->getCode());
        }
    }

    public function cancel(Request $request) {
        $validator = Validator::make($request->all(), [
            'compra_id' => 'required|integer'
        ]);        

        if ($validator->fails()) {
            return $this::enviarRespostaErro('Erros de validação', $validator->errors());
        }

        $user = $request->user();
        
        $compra = Compra::find($request->compra_id);
        if (!$compra) {
            return $this::enviarRespostaErro('Compra inválida');
        }

        $seller = new Seller(env('PICPAY_TOKEN'), env('PICPAY_SELLER_TOKEN'));
        
        try {
            $cancelRequest = new CancelRequest($seller, $compra->codigo, $compra->autorizacao);
            $cancelResponse = $cancelRequest->execute();

            return $this::enviarRespostaSucesso($cancelResponse, 'Pedido cancelado/reembolsado com sucesso!', 204);
        } catch (RequestException $e) {
            return $this::enviarRespostaErro($e->getErrors(), $e->getMessage(), $e->getCode());
        }    
    }

    public function notification(Request $request) {
        $validator = Validator::make($request->all(), [
            'referenceId' => 'required'
        ]);

        if ($validator->fails()) {
            return $this::enviarRespostaErro('Erros de validação', $validator->errors());
        }

        $compra = Compra::all()->where('codigo', $request->referenceId)->last();
        if (!$compra) {
            return $this::enviarRespostaErro('Compra inválida');
        }

        $seller = new Seller(env('PICPAY_TOKEN'), env('PICPAY_SELLER_TOKEN'));
        
        try {
            //$remoteNotification = new NotificationRemoteRequest(`{"referenceId": $compra->codigo, "authorizationId": $request->authorizationId}`);
            //$authorizationId = $remoteNotification->getAuthorizationId();

            $status = new StatusRequest($seller, $compra->codigo);
            $resultado = $status->execute();

            CompraController::update($compra->codigo, $resultado->authorizationId, $resultado->status);

            return $this::enviarRespostaSucesso($resultado, 'Pagamento atualizado com sucesso', 200);
        } catch (RequestException $e) {
            return $this::enviarRespostaErro($e->getErrors(), $e->getMessage(), $e->getCode());
        }
    }

    private function gerarCodigo() {
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXIZ';
        $tamanho = strlen($caracteres);
        $codigo = '';
        for ($i = 0; $i < 15; $i++) {
            $caracter = $caracteres[mt_rand(0, $tamanho - 1)];
            $codigo .= $caracter;
        }

        return $codigo;
    }
}
