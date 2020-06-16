<?php
namespace App\Http\Controllers\API;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->enviarRespostaErro('Erros de validação.', $validator->errors());
        }
        // tenta fazer login com os dados recebidos, se der certo
        if (auth()->attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = auth()->user();
            // se o usuario nao tiver um token valido cria um token novo
            if ($user->token() == '') {
                $token = $user->createToken('web')->accessToken;
            }
            // se ja tiver token valido usa ele
            else {
                $token = $user->token();
            }
            // retorna o usuario e o token
            return $this->enviarRespostaSucesso(['usuario' => $user, 'token' => $token ]);
        }
        // se nao conseguir fazer login com as credenciais recebidas
        return $this->enviarRespostaErro('Credenciais inválidas', null, 401);
    }

    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function registro(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required|max:255',
            'lastName' => 'required|max:255',
            'cpf' => 'required|regex:/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/|unique:users',//cpf no formato 123.456.789-10
            'telefone' => 'required|regex:/\+\d{2}\s\d{2}\s\d{4,5}\-\d{4}/|unique:users',//telefone no formato +55 27 12345-6789
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->enviarRespostaErro('Erros de validação.', $validator->errors(), 400);
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['codigo'] = $this->gerarCodigo();
        $usuario = User::create($input);
        $dadosResposta['token'] = $usuario->createToken('web')->accessToken;
        $dadosResposta['user'] = $usuario;
        return $this->enviarRespostaSucesso($dadosResposta, 'Usuário registrado com sucesso.', 201);
    }

    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function meuPerfil(Request $request)
    {
        if ($request->user() == null) {
            return $this->enviarRespostaErro('Usuário não encontrado.', null,400);
        }
        return $this->enviarRespostaSucesso($request->user(), 'Usuário encontrado.', 200);
    }

    protected function gerarCodigo()
    {
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $tam = strlen($caracteres);
        $codigo = '';
        for($i = 0; $i < 150; $i++)
        {
            $caracter = $caracteres[mt_rand(0, $tam -1)];
            $codigo .= $caracter;
        }
        return $codigo;
    }
}
