<template>
  <v-container>
    <v-layout column justify-center align-center fill-height>
      <h1>Resposta da api:</h1>
      <p>
        {{ dados }}
      </p>
      <v-btn color="primary" @click="callAPI">
        Testar api
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
  // layout para ser usado, se não for especificado usa o layout default
  layout: 'default',
  // metodos do componente, vem com o metodo de testar api, apenas para demonstrar como fazer chamadas
  methods: {
    callAPI() {
      this.$nuxt.$loading.start()
      this.$axios
        .get('teste')
        .then((response) => {
          this.dados = response.data
          this.$toast.show('Exemplo de toast show')
          this.$toast.success('Exemplo de toast success')
          this.$toast.info('Exemplo de toast info')
          this.$toast.error('Exemplo de toast error')
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
