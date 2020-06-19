<?php

// https://sandbox.pagseguro.uol.com.br/v2/checkout/payment.html?code=6EC41F9F91919A6774BA6F8B6F73EC41
// User comprador de teste e fazer a compra
// https://github.com/muvasco/picpay-php-sdk

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController;
use Illuminate\Http\Exceptions\HttpResponseException;
use Exception;
use PagSeguro\Library as PagSeguro;
use PagSeguro\Configuration\Configure as PagSeguroConfig;
use PagSeguro\Domains\Requests\Payment as Payment;
use App\User;
use App\Produto;
use PagSeguro\Services\Transactions as Transactions;
use Illuminate\Support\Facades\Validator;

try {
    PagSeguro::initialize();
    PagSeguro::cmsVersion()->setName("Nome")->setRelease("1.0.0");
    PagSeguro::moduleVersion()->setName("Nome")->setRelease("1.0.0");
} catch (Exception $e) {
    throw new HttpResponseException (
        response()->json(
            [
                'mensagem' => 'Erro inicializando o PagSeguro',
                'errosSecundários' => $e->getMessage()
            ],
            400
        )
    );
}

PagSeguroConfig::setEnvironment(env('PAGSEGURO_AMBIENTE'));
PagSeguroConfig::setAccountCredentials(env('PAGSEGURO_EMAIL'), env('PAGSEGURO_TOKEN'));
PagSeguroConfig::setCharset('UTF-8');
PagSeguroConfig::setLog(true, '../storage/logs/PagSeguro.log');


class PagSeguroController extends BaseController
{
    const statusInfo = [
        'Aguardando pagamento',
        'Em análise',
        'Paga',
        'Disponível',
        'Em disputa',
        'Devolvida',
        'Cancelada',
        'Debitado',
        'Retenção temporária'
    ];

    public function checkout(Request $request) {
        $validator = Validator::make($request->all(), [
            // 'items' => 'required|array',
            // 'items.*.id' => 'required|integer',
            // 'items.*.quantidade' => 'required|integer',
            // 'user_id' => 'required|integer'
            'item_id' => 'required|integer',
            'quantidade' => 'required|integer'
        ]);

        if($validator->fails()) {
            return $this::enviarRespostaErro('Erros de validação', $validator->errors());
        }

        // $user = User::find($request->user_id);
        // if(!$user) {
        //     return $this::enviarRespostaErro('Usuário inválido!');
        // }
        $user = $request->user();

        $pagamento = new Payment();
        
        $produto = Produto::find($request->item_id);
        if(!$produto) {
            return $this::enviarRespostaErro('Produto inválido!');
        }

        $pagamento->addItems()->withParameters(
            $produto->id,
            $produto->descricao,
            $request->quantidade,
            $produto->preco
        );

        $pagamento->setCurrency('BRL');
        $pagamento->setReference($user->codigo);

        try {
            $onlyCheckoutCode = true;
            $resultado = $pagamento->register(PagSeguroConfig::getAccountCredentials(), $onlyCheckoutCode);
            $codigo = $resultado->getCode();
            return $this::enviarRespostaSucesso($codigo, 'Código gerado com sucesso');
        } catch (Exception $e) {
            return $this::enviarRespostaErro('Ocorreu um erro gerando o código', $e->getMessage());
        }
    }

    public function transaction(Request $request) {
        $validator = Validator::make($request->all(), [
            'codigo' => 'required'
        ]);

        if ($validator->fails()) {
            return $this::enviarRespostaErro('Erros de validação!');
        }

        $user = $request->user();
        $codigoUser = $user->codigo;

        $dataInicial = date("Y-m-d\TG:i", strtotime("-1 months"));

        $options = [
            'initial_date' => $dataInicial,
            'page' => 1,
            'max_per_page' => 900
        ];

        try {
            $transacao = Transactions\Search\Code::search(PagSeguroConfig::getAccountCredentials(), $request->codigo, $options);

            $lista = collect();
            if ($transacao) {
                $data = $transacao->getDate();
                $codigoTrans = $transacao->getCode();
                $valor = $transacao->getGrossAmount();
                $status = $this::statusInfo[intval($transacao->getStatus()) -1];
                
                $codigoRef = $transacao->getReference();
                if ($codigoRef != $codigoUser) {
                    return $this::enviarRespostaErro('Ocorreu um erro listando a transação, código de usuário inválido');
                }

                $itemsObj = $transacao->getItems();
                $items = collect();
                foreach ($itemsObj as $item) {
                    $id = $item->getId();
                    $descricao = $item->getDescription();
                    $quantidade = $item->getQuantity();
                    $valor = $item->getAmount();
                    $itemsCollect = collect([
                        'id' => $id,
                        'descricao' => $descricao,
                        'quantidade' => $quantidade,
                        'valor' => $valor
                    ]);
                    $items = $items->push($itemsCollect);
                }

                $trans = collect([
                    'data' => $data,
                    'codigoTrans' => $codigoTrans,
                    'valor' => $valor,
                    'status' => $status,
                    'items' => $items
                ]);
                $lista = $lista->push($trans);
            }
            
            return $this::enviarRespostaSucesso($lista, 'Transação encontrada!');
        } catch (Exception $e) {
            return $this::enviarRespostaErro('Ocorreu um erro buscando essa transação', $e->getMessage());
        }
    } 

    public function cancel(Request $request) {
        $validator = Validator::make($request->all(), [
            'codigo' => 'required'
        ]);

        if ($validator->fails()) {
            return $this::enviarRespostaErro('Erros de validação!');
        }

        $user = $request->user();
        $codigoUser = $user->codigo;
        
        try {
            $transacao = Transactions\Search\Code::search(PagSeguroConfig::getAccountCredentials(), $request->codigo);
            
            $codigoRef = $transacao->getReference();
            if ($codigoRef != $codigoUser) {
                return $this::enviarRespostaErro('Ocorreu um erro listando a transação, código de usuário inválido');
            }
            
            $cancel = Transactions\Cancel::create(PagSeguroConfig::getAccountCredentials(), $request->codigo);
            
            return $this::enviarRespostaSucesso('Transação cancelada!');
        } catch (Exception $e) {
            return $this::enviarRespostaErro('Ocorreu um erro cancelando essa transação', $e->getMessage());       
        }
    }

    public function refund(Request $request) {
        $validator = Validator::make($request->all(), [
            'codigo' => 'required'
        ]);

        if ($validator->fails()) {
            return $this::enviarRespostaErro('Erros de validação!');
        }

        $user = $request->user();
        $codigoUser = $user->codigo;

        try {
            $transacao = Transactions\Search\Code::search(PagSeguroConfig::getAccountCredentials(), $request->codigo);
            
            $codigoRef = $transacao->getReference();
            if ($codigoRef != $codigoUser) {
                return $this::enviarRespostaErro('Ocorreu um erro listando a transação, código de usuário inválido');
            }
            
            $refund = Transactions\Refund::create(PagSeguroConfig::getAccountCredentials(), $request->codigo);
            
            return $this::enviarRespostaSucesso('Transação reembolsada!');
        } catch (Exception $e) {
            return $this::enviarRespostaErro('Ocorreu um erro reembolsando essa transação', $e->getMessage());       
        }
    }

    public function transactions(Request $request) {
        $user = $request->user();

        $dataInicial = date("Y-m-d\TG:i", strtotime("-1 months"));

        $options = [
            'initial_date' => $dataInicial,
            'page' => 1,
            'max_per_page' => 900
        ];

        $codigo = $user->codigo;

        try {
            $resultado = Transactions\Search\Reference::search(PagSeguroConfig::getAccountCredentials(), $codigo, $options);
            $transacoes = $resultado->getTransactions();

            $lista = collect();
            if ($transacoes) {
                foreach ($transacoes as $transacao) {
                    $data = $transacao->getDate();
                    $codigoTrans = $transacao->getCode();
                    $valor = $transacao->getGrossAmount();
                    $status = $this::statusInfo[intval($transacao->getStatus()) -1];

                    $trans = collect([
                        'data' => $data,
                        'codigoTrans' => $codigoTrans,
                        'valor' => $valor,
                        'status' => $status
                    ]);
                    $lista = $lista->push($trans);
                }
            }
            return $this::enviarRespostaSucesso($lista, 'Transações listadas com sucesso!');
        } catch (Exception $e) {
            return $this::enviarRespostaErro('Ocorreu um erro listando as compras', $e->getMessage());
        }
    }

    public function notification(Request $request) {
        $validator = Validator::make($request->all(), [
            'notificationCode' => 'required',
            'notificationType' => 'required'
        ]);

        if ($validator->fails()) {
            return $this::enviarRespostaErro('Erros de validação!');
        }

        try {
            if (\PagSeguro\Helpers\Xhr::hasPost()) {
                $transacao = Transactions\Notification::check(PagSeguroConfig::getAccountCredentials());
            } else {
                throw new \InvalidArgumentException($_POST);
            }

            $lista = collect();
            if ($transacao) {
                $data = $transacao->getDate();
                $codigoTrans = $transacao->getCode();
                $valor = $transacao->getGrossAmount();
                $status = $this::statusInfo[intval($transacao->getStatus()) -1];
                
                $itemsObj = $transacao->getItems();
                $items = collect();
                foreach ($itemsObj as $item) {
                    $id = $item->getId();
                    $descricao = $item->getDescription();
                    $quantidade = $item->getQuantity();
                    $valor = $item->getAmount();
                    $itemsCollect = collect([
                        'id' => $id,
                        'descricao' => $descricao,
                        'quantidade' => $quantidade,
                        'valor' => $valor
                    ]);
                    $items = $items->push($itemsCollect);
                }

                $trans = collect([
                    'data' => $data,
                    'codigoTrans' => $codigoTrans,
                    'valor' => $valor,
                    'status' => $status,
                    'items' => $items
                ]);
                $lista = $lista->push($trans);
            }
            
            return $this::enviarRespostaSucesso($lista, 'Transação checada!');   
        } catch (Exception $e) {
            return $this::enviarRespostaErro('Ocorreu um erro checando a transação', $e->getMessage());
        }
    }
}
