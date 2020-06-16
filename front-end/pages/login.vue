<template>
  <div>
    <title-auth titulo="Login" />
    <input-auth v-model="email" placeholder="Email" />
    <input-auth v-model="password" placeholder="Senha" type="password" />
    <v-flex class="text-xs-right">
      <nuxt-link class="body-1 grey--text" to="/esqueci-a-senha">
        Esqueceu a senha?
      </nuxt-link>
    </v-flex>
    <div @click="realizarLogin">
      Entrar
    </div>
  </div>
</template>

<script>
import InputAuth from '~/components/inputs/InputAuth'
import TitleAuth from '~/components/sections/auth/TitleAuth'

export default {
  components: {
    TitleAuth,
    InputAuth
  },
  head() {
    return {
      title: 'Login',
      meta: [
        {
          hid: 'description',
          name: 'description',
          content: 'Página para fazer login no sistema'
        }
      ]
    }
  },
  layout: 'auth',
  middleware: ['guest'],
  data() {
    return {
      email: '',
      password: ''
    }
  },
  computed: {},
  methods: {
    async realizarLogin() {
      this.$nuxt.$loading.start()
      await this.$auth
        .loginWith('local', {
          data: {
            email: this.email,
            password: this.password
          }
        })
        .then(() => {
          this.$router.push('/dashboard')
        })
        .catch(({ response }) => {
          const { mensagem, errosSecundarios: erros } = response.data
          const listaErros = erros
            ? `\n ${Object.values(erros).join('\n')}`
            : ''
          this.$toast.error(`${mensagem}${listaErros}`, { duration: 5000 })
        })
        .finally(() => {
          this.$nuxt.$loading.finish()
        })
    }
  }
}
</script>

<style lang="stylus" scoped>
.esqueceu-senha
  width 100%
  text-align right
</style>
