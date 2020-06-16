<?php

// https://sandbox.pagseguro.uol.com.br/v2/checkout/payment.html?code=
// User comprador de teste e fazer a compra

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

PagSeguro::setEnviroment(env('PAGSEGURO_AMBIENTE'));
PagSeguro::setAccountCredentials(env('PAGSEGURO_EMAIL'), env('PAGSEGURO_TOKEN'));
PagSeguro::setCharset('UTF-8');
PagSeguro::setLog(true, '../storage/logs/PagSeguro.log');


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
            'quantidade' => 'required|integer',
            'user_id' => 'required|integer'
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
            $produto->quantidade,
            $produto->preco
        );

        $pagamento->setCurrency('BRL');
        $pagamento->setReference($user->codigo);

        try {
            $onlyCheckoutCode = true;
            $resultado = $pagamento->register(PagSeguro::getAccountCredentials(), $onlyCheckoutCode);
            $codigo = $resultado->getCode();
            return $this::enviarRespostaSucesso($codigo, 'Código gerado com sucesso');
        } catch (Exception $e) {
            return $this::enviarRespostaErro('Ocorreu um erro gerando o código', $e->getMessage());
        }
    }

    public function transactions(Request $request) {
        $user = $request->user();

        $dataInicial = date("Y-m-d\TG:i", strtotime("-1 months"));

        $options = [
            'inicital_date' => $dataInicial,
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
}
