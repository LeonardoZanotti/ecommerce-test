# Testando ecommerce

Testando implementação das APIs do PagSeguro e do PicPay

## Requisitos
* Estou me baseando em usuários Linux, então seria bom usá-lo. Para Windows os processos são diferentes.
* Laravel 5.7.29
* Composer
* MariaDB 10.4
* PHP 7.3
* Git
* Node (a versão depende do seu sistema operacional, para Linux Mint 19.3 usei Node 10.x)

## Instalação
### Dentro da pasta ```back-end``` execute:

```bash
composer install

sudo mysql -u root

create database capacitacao;

CREATE USER 'newuser'@'localhost' IDENTIFIED BY 'user_password';
// substituir newuser pelo seu usuario e password pela senha

GRANT ALL PRIVILEGES ON *.* TO 'newuser'@'localhost';

exit

cp .env.example .env

nano .env
//configurar seu .env com seu "DB_USERNAME = seu_usuario", "DB_DATABASE =  capacitacao" e "DB_PASSWORD = sua_senha"

php artisan key:generate

php artisan migrate:fresh --seed

php artisan storage:link

php artisan passport:install
// talvez seja necessário usar um require antes com a versão do passport

php artisan serve
// seu projeto estará em localhost:8000
```

### Dentro da pasta ```front-end``` execute:

```bash
npm install

npm audit fix

npm run lint -- --fix

npm run dev
// o site estará em localhost:3000
// dashboard: localhost:3000/dashboard
// o login e senha são os do seeder 
```

### Testes
Usei o [postman](https://www.postman.com/) para testar o projeto mas o [Insomnia](https://insomnia.rest/) também deve servir.

### Observações:
* A API do PagSeguro não considera os adicionais que o PagSeguro cobra por utilizar o método de pagamento 'boleto' ou por parcelar o pagamento via cartão de crédito.

* A API do Picpay não foi testada pois é necessário baixar o app deles e efetuar uma compra real cadastrando o seu cartão de crédito para testar.

* Eu não fiz o front-end, apenas o copiei pronto, portanto ele está com alguns problemas que não sei resolver (ainda) mas dá para cadastrar novos produtos e efetuar as transações através dele.

* O front-end contempla apenas a API do PagSeguro.

## Leonardo Zanotti