# Ecommerce do PagSeguro

> Parte do Front-end do ecommerce utilizando a plataforma PagSeguro feito em VUE.JS e NUXT

Arquivos Importantes: 

1. Componente product.vue
2. Page index.vue
3. Page dashboard.vue
4. Page transacoes.vue

1 Componente product.vue

Aqui definimos o esqueleto do nosso card de um produto, deixando "espaços" para a utilização de 5 props fundamentais: 
titulo, descricao, preco, imagem, produtoID.

Nesse arquivo importamos a API de pagamento JavaScript da PagSeguro contendo os métodos e o componente de compra.

Contém duas funções: a função checkout que irá gerar um código de transação fundamental para a compra e a função 
pagseguro advinda do link que importamos anterioremente responsável por aparecer a PagSeguroLightbox e realizar a compra.

** é necessário desabilitar o ESLINT para funcionar a chamada de função de um script javascript importado.

Guia de Integração OFICIAL: http://download.uol.com.br/pagseguro/docs/pagseguro-checkout-lightbox.pdf

2 Page index.vue

Responsável pela listagem de todos os produtos cadastrados e passagem de props para o componente product.

3 Page dashboard.vue

Página responsável pela criação, edição, listagem e exclusão de um produto.

4 Page transacoes.vue

Lista todas as transações ocorridas após a compra de um produto. Contém o Status do Pagamento, o horário da transação e o valor.
