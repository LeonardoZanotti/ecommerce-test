<template>
  <v-container>
    <v-layout>
      <v-flex>
        <span class="font-weight-thin display-2">PRODUTOS</span>
      </v-flex>
    </v-layout>
    <v-divider color="grey" />
    <v-card-title>
      <v-btn
        class="mt-10"
        color="#2d6760"
        outlined
        light
        @click.stop="modalLocal = !modalLocal"
      >
        NOVO PRODUTO
      </v-btn>
      <v-spacer></v-spacer>
      <v-text-field
        v-model="search"
        append-icon="mdi-magnify"
        label="Pesquisar"
        single-line
        hide-details
      ></v-text-field>
    </v-card-title>
    <v-dialog
      v-model="modalLocal"
      max-width="800px"
      no-click-animation
      persistent
    >
      <!--Inicio modal de editar-->
      <v-card>
        <v-card-title>
          <span class="headline">Insira dados do Produto</span>
        </v-card-title>
        <v-card-text>
          <v-container grid-list-md>
            <v-layout wrap column>
              <v-flex xs12 sm6 md4>
                <v-form ref="form" v-model="validation">
                  <v-text-field
                    v-model="produto.titulo"
                    :rules="[(v) => !!v || 'Campo Obrigatório']"
                    color="cyan darken-2"
                    label="Produto"
                  />
                  <v-text-field
                    v-model="produto.descricao"
                    :rules="[(v) => !!v || 'Campo Obrigatório']"
                    color="cyan darken-2"
                    label="Descrição"
                  />
                  <v-text-field
                    v-model="produto.preco"
                    :rules="[(v) => !!v || 'Campo Obrigatório']"
                    color="cyan darken-2"
                    label="Preço"
                  />
                  <v-text-field
                    v-model="imagemNome"
                    label="Imagem para o Produto"
                    solo
                    readonly
                    @click="$refs.imginput.click()"
                  />
                  <input
                    ref="imginput"
                    type="file"
                    accept="image/*"
                    style="display: none;"
                    @change="imagemEscolhida"
                  />
                </v-form>
              </v-flex>
            </v-layout>
          </v-container>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn color="#FF3D00" text @click="onClose">
            Cancelar
          </v-btn>
          <v-btn color="#FF3D00" text @click="reset">
            Reiniciar
          </v-btn>
          <v-btn color="#FF3D00" text @click="onSubmit">
            Salvar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-layout class="mt-4">
      <v-flex>
        <v-data-table
          :headers="headers"
          :items="produtosInfo"
          :search="search"
          class="elevation-3"
        >
          <template v-slot:item.action="{ item }">
            <v-icon medium class="mr-2" color="primary" @click="editItem(item)">
              mdi-circle-edit-outline
            </v-icon>
            <v-icon medium color="error" @click="deleteProduto(item)">
              mdi-delete
            </v-icon>
          </template>
        </v-data-table>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
export default {
  layout: 'dashboard',
  data() {
    return {
      search: '',
      modalLocal: false,
      validation: true,
      editedIndex: -1,
      headers: [
        {
          text: 'Produto',
          align: 'left',
          sortable: true,
          value: 'titulo'
        },
        { text: 'Descrição', sortable: true, value: 'descricao' },
        { text: 'Preço', sortable: true, value: 'preco' },
        { text: '', value: 'action', sortable: false }
      ],
      produtos: [],
      imagens: [],
      produto: {
        id: 0,
        preco: '',
        titulo: '',
        descricao: '',
        imagem: ''
      },
      defaultItem: {
        id: 0,
        titulo: '',
        preco: '',
        descricao: '',
        imagem: ''
      }
    }
  },
  computed: {
    // funcao MAP JS, serve para pegar elementos específicos da API
    produtosInfo() {
      return this.produtos.map((c) => ({
        id: c.id,
        titulo: c.titulo,
        descricao: c.descricao,
        preco: c.preco,
        imagem: c.imagem,
        imagemNome: c.imagem
      }))
    }
  },
  asyncData(context) {
    return context.app.$axios
      .get('/Produtos')
      .then((res) => ({ produtos: res.data.dados }))
  },
  methods: {
    onSubmit() {
      if (!this.$refs.form.validate()) {
        return
      }
      const path = `/${
        this.editedIndex === -1 ? 'novoProduto' : 'atualizaProduto'
      }`
      const formproduto = new FormData()
      formproduto.append('id', this.produto.id)
      formproduto.append('titulo', this.produto.titulo)
      formproduto.append('descricao', this.produto.descricao)
      formproduto.append('preco', this.produto.preco)
      if (this.produto.imagem) {
        formproduto.append('imagem', this.produto.imagem)
      }
      this.$axios
        .$post(path, formproduto, {
          header: { 'Content-Type': 'multipart/form-data' }
        })
        .then(() => {
          this.modalLocal = false
          window.location.reload(true)
        })
        .catch(({ response }) => {
          const { mensagem, errosSecundarios: erros } = response.data
          const listaErros = erros
            ? `\n ${Object.values(erros).join('\n')}`
            : ''
          this.$toast.error(`${mensagem}${listaErros}`, { duration: 5000 })
        })
    },

    editItem(item) {
      this.modalLocal = true
      this.editedIndex = item.id
      this.produto = Object.assign({}, item)
      this.produto.imagem = ''
      this.imagemNome = item.imagem
    },

    deleteProduto(item) {
      const ok = window.confirm(
        'Você tem certeza de que deseja excluir esse Produto?'
      )
      if (ok) {
        this.$axios
          .post('deletaProduto', {
            id: item.id
          })
          .then(() => {
            this.produtos = this.produtos.filter((e) => e.id !== item.id)
          })
      }
    },

    onClose() {
      this.modalLocal = false
      this.editedIndex = -1
      this.$refs.form.reset()
    },

    refresh() {
      this.$axios
        .get('/Produtos')
        .then((res) => {
          this.produtos = res.data.dados
        })
        .catch(() => ({}))
    },

    imagemEscolhida(e) {
      const imagem = e.target.files
      if (imagem[0] !== undefined) {
        // exibe nome da imagem selecionada
        this.imagemNome = imagem[0].name
        if (this.imagemNome.lastIndexOf('.') <= 0) {
          return
        }
        ;[this.produto.imagem] = imagem
      } else {
        this.produto.imagem = ''
        this.imagemNome = ''
      }
    }
  },
  middleware: 'auth'
}
</script>

<style></style>
