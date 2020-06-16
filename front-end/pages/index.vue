<template>
  <v-container>
    <v-layout column justify-center align-center fill-height>
      <p>
        1) Olhem e pesquisem sobre a estrutura das Pastas NUXT antes de iniciar
      </p>
      <p>2) Utilizem o Vuetify, contém um monte de tags e scripts prontos</p>
      <p>
        3) Vejam os Projetos Antigos e se baseie neles Ex:. AfroCuritiba,
        Humanidades
      </p>
      <p>
        4) Para pegar dados do backend, pesquisem sobre AXIOS e vejam o modelo
        abaixo de testes
      </p>
      <p>
        5) Para remover o layout dessa página, removam o conteúdo do layout
        DEFAULT
      </p>
      <p>
        6) **Não hesitem em perguntar, somos todos ECOMP**
      </p>
      <h1>Resposta da api:</h1>
      <p>
        {{ dados }}
      </p>
      <v-btn color="primary" @click="callAPI">
        Testar api com AXIOS
      </v-btn>
    </v-layout>
  </v-container>
</template>

<script>
export default {
  // funcao que pega dados que vem do backend antes da pagina carregar
  // o return dessa função é appended ao data da página
  asyncData(context) {
    return context.app.$axios.get('teste').then((res) => ({
      dados: res.data ? res.data : 'Backend não retornou nenhum dado'
    }))
  },
  // infos da página
  head() {
    return {
      title: 'Nome da página que aparece na aba no navegador',
      meta: [
        {
          hid: 'description',
          name: 'description',
          content: 'Página para ser usada como template para criar outras.'
        }
      ]
    }
  },
  // pega o layout que tem na pasta layout e joga nessa page, se não for especificado usa o layout default
  layout: 'default',
  // metodos do componente, vem com o metodo de testar api, apenas para demonstrar como fazer chamadas e pegar os dados
  methods: {
    callAPI() {
      this.$nuxt.$loading.start()
      this.$axios
        .get('teste')
        .then((response) => {
          this.dados = response.data
        })
        .catch((response) => {
          this.dados = response
        })
        .finally(() => {
          this.$nuxt.$loading.finish()
        })
    }
  }
}
</script>

<style scoped></style>
