<template>
  <div>
    <banner />
    <v-container>
      <v-row mt-4>
        <v-col
          v-for="item in products"
          :key="item.products"
          cols="3"
          class="ma-15"
        >
          <product
            :produto-id="item.id"
            :descricao="item.descricao"
            :preco="item.preco"
            :titulo="item.titulo"
            :imagem="item.imagem"
          />
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script>
import Banner from '../components/sections/Banner.vue'
import Product from '../components/product/product.vue'

export default {
  components: { Banner, Product },
  head() {
    return {
      title: 'Payment Page'
    }
  },
  data() {
    return {
      products: [],
      quantity: '1',
      codigo: ''
    }
  },
  computed: {
    productInfo() {
      return this.products.map((p) => ({
        id: p.id,
        preco: p.preco,
        imagem: p.imagem,
        titulo: p.titulo,
        descricao: p.descricao
      }))
    }
  },
  async asyncData({ $axios }) {
    const [productsRes] = await Promise.all([$axios.get('/Produtos')])
    return {
      products: productsRes.data.dados
    }
  }
}
</script>

<style scoped></style>
