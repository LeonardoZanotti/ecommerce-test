<template>
  <v-card class="elevation-4">
    <v-img :src="`http://127.0.0.1:8000/storage/${imagem}`" height="200px">
    </v-img>
    <v-card-title primary-title>
      <div>
        <div class="product-title mt-1">{{ titulo }}</div>
        <v-card-text class="grey--text">{{ descricao }} </v-card-text>
      </div>
    </v-card-title>
    <v-row>
      <v-col cols="7">
        <span class="text-left ml-5 product-price">R$ {{ preco }}</span>
      </v-col>
      <v-col class="mr-8">
        <v-select
          v-model="quantity"
          :items="[1, 2, 3, 4, 5, 6, 7]"
          label="Quantidade"
          outlined
        ></v-select>
      </v-col>
    </v-row>
    <v-card-actions>
      <v-btn
        color="green darken-2 white--text"
        class="ml-1"
        x-large
        @click="checkout()"
      >
        Comprar
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
export default {
  name: 'Products',
  props: {
    descricao: {
      type: String,
      required: true
    },
    imagem: {
      type: String,
      required: false
    },
    preco: {
      type: Number,
      required: true
    },
    titulo: {
      type: String,
      required: true
    },
    produtoId: {
      type: Number,
      required: true
    }
  },
  head() {
    return {
      // Importa na página de pagamento API JavaScript contendo os métodos e o componente
      script: [
        {
          type: 'text/javascript',
          src:
            'https://stc.sandbox.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.lightbox.js'
        }
      ]
    }
  },
  data() {
    return {
      quantity: '',
      codigo: ''
    }
  },
  methods: {
    checkout() {
      //  váriavel com todos os dados da compra
      const checkoutData = {
        quantidade: this.quantity,
        item_id: this.produtoId
      }
      this.$axios
        .$post('novaCompra', checkoutData)
        .then((res) => {
          //  Armazeno em uma variável o código que a rota novaCompra retorna
          this.codigo = res.dados
          //  Chamo a função responsável por aparecer o componente do pagseguro
          this.pagseguro()
        })
        .catch(({ response }) => {
          alert('Falha, ', response)
        })
    },
    pagseguro() {
      // Desabilita o eslint para funcionar a chamada de funções presentes no script importado
      const isOpenLightbox = PagSeguroLightbox( // eslint-disable-line
        // Passo como paramêtro o código gerado anteriormente
        this.codigo,
        {
          success(transactionCode) {
            alert('success - ' + transactionCode)
          },
          abort() {
            alert('Compra Cancelada')
          }
        }
      )
      // IF para caso o navegador seja incompátivel com o script do PagSeguro
      if (!isOpenLightbox) {
        location.href =
          'https://sandbox.pagseguro.uol.com.br/v2/checkout/payment.html?code=' +
          this.codigo
      }
      return 0
    }
  }
}
</script>
<style scoped>
.product-title {
  font-size: 35px;
}
.product-price {
  font-size: 23px;
}
</style>
