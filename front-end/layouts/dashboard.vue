<template>
  <v-app>
    <v-navigation-drawer v-model="drawer" app>
      <v-list>
        <v-list-item-group>
          <v-list-item v-for="(item, i) in items" :key="i" :to="item.link">
            <v-list-item-icon>
              <v-icon v-text="item.icon"></v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title v-text="item.text"></v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list-item-group>
      </v-list>
    </v-navigation-drawer>

    <v-app-bar app color="#2d6760" dark>
      <v-app-bar-nav-icon @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
      <v-toolbar-title>Painel do Administrador</v-toolbar-title>
      <v-spacer></v-spacer>
      <v-btn @click="redirecionar('/sistema')">
        Sair
      </v-btn>
    </v-app-bar>
    <v-content>
      <nuxt />
    </v-content>
    <v-footer color="#2d6760" app inset>
      <span class="white--text">&copy; Ecomp 2020</span>
    </v-footer>
  </v-app>
</template>

<script>
export default {
  head() {
    return {
      title: 'Dashboard',
      meta: [
        {
          hid: 'description',
          name: 'description',
          content: ''
        }
      ]
    }
  },
  data: () => ({
    drawer: null,
    items: [
      {
        icon: 'mdi-store',
        text: 'Produtos',
        link: '/dashboard'
      },
      {
        icon: 'mdi-basket',
        text: 'Transações',
        link: '/transacoes'
      }
    ]
  }),
  methods: {
    redirecionar(link) {
      this.$router.push({
        path: link
      })
    }
  }
}
</script>
