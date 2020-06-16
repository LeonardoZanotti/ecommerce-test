<template>
  <div>
    <v-dialog
      v-model="dialogPrincipalAberto"
      :fullscreen="$vuetify.breakpoint.xsOnly"
      :scrollable="$vuetify.breakpoint.smAndUp"
      persistent
      no-click-animation
    >
      <v-card>
        <v-toolbar dark color="primary">
          <v-toolbar-title>Galeria</v-toolbar-title>
          <v-spacer />
          <v-toolbar-items>
            <v-btn icon dark @click="toggleDialog">
              <v-icon>close</v-icon>
            </v-btn>
          </v-toolbar-items>
        </v-toolbar>
        <v-divider />
        <v-layout :column="$vuetify.breakpoint.xsOnly" class="content">
          <v-flex v-show="sidebarAberta" class="preview-form">
            <div
              v-show="modoEditar === false && modoCriar === false"
              class="top-btns"
            >
              <v-btn
                small
                fab
                color="grey lighten-3"
                @click="abrirImagemEmNovaAba"
              >
                <v-icon color="grey darken-1">
                  open_in_new
                </v-icon>
              </v-btn>
              <v-btn
                small
                fab
                color="grey lighten-3"
                @click="arquivoSelecionado = arquivoVazio"
              >
                <v-icon color="grey darken-1">
                  close
                </v-icon>
              </v-btn>
            </div>
            <template v-if="arquivoSelecionado && arquivoSelecionado.id">
              <img
                :src="
                  arquivoSelecionado && arquivoSelecionado.id !== undefined
                    ? `api/arquivos/${arquivoSelecionado.id}`
                    : ''
                "
                class="mx-auto preview-img"
              />
            </template>
            <template
              v-else-if="
                arquivoSelecionado && arquivoSelecionado.caminhoTemporario
              "
            >
              <img
                :src="arquivoSelecionado.caminhoTemporario"
                :alt="arquivoSelecionado.alt"
                class="mx-auto preview-img"
              />
            </template>
            <template v-else>
              <v-btn class="mt-4" color="primary" @click="onUploadAtivado">
                Upload
              </v-btn>
            </template>
            <v-container>
              <v-text-field
                v-model="arquivoSelecionado.nome"
                :disabled="modoEditar === false && modoCriar === false"
                label="Nome"
                box
                hint="É usado como legenda da arquivo."
              />
              <v-text-field
                v-model="arquivoSelecionado.alt"
                :disabled="modoEditar === false && modoCriar === false"
                label="Alt"
                box
                hint="Lido para os usuários que não enxergam a arquivo."
              />
              <v-text-field
                v-model="arquivoSelecionado.descricao"
                :disabled="modoEditar === false && modoCriar === false"
                label="Descrição"
                box
                hint="Usada apenas para facilitar buscas no sistema."
              />
              <v-layout justify-space-around align-center row pt-2>
                <template v-if="modoEditar === false && modoCriar === false">
                  <v-btn depressed color="primary" @click="onEscolherAtivado">
                    Escolher
                  </v-btn>
                  <v-btn
                    outline
                    color="primary darken-2"
                    @click="iniciarEdicao"
                  >
                    Editar
                  </v-btn>
                </template>
                <template v-else-if="modoCriar === true">
                  <v-btn
                    depressed
                    color="primary"
                    :disabled="!todosCamposPreenchidos"
                    @click="salvarCriacao"
                  >
                    Salvar
                  </v-btn>
                  <v-btn outline color="grey darken-2" @click="cancelarCriacao">
                    Cancelar
                  </v-btn>
                </template>
                <template v-else>
                  <v-btn depressed color="primary" @click="salvarEdicao">
                    Salvar
                  </v-btn>
                  <v-btn outline color="grey darken-2" @click="cancelarEdicao">
                    Cancelar
                  </v-btn>
                  <v-btn
                    icon
                    outline
                    color="grey darken-2"
                    @click="confirmarRemoverAberto = true"
                  >
                    <v-icon small>
                      delete
                    </v-icon>
                  </v-btn>
                </template>
              </v-layout>
            </v-container>
          </v-flex>
          <v-flex class="wrapper-grid-fotos">
            <v-flex class="pt-4">
              <v-responsive class="mx-auto search" max-width="300">
                <v-btn
                  v-show="modoCriar === false && modoEditar === false"
                  small
                  fab
                  depressed
                  color="primary"
                  @click="
                    ;(arquivoSelecionado = arquivoVazio), (modoCriar = true)
                  "
                >
                  <v-icon>add</v-icon>
                </v-btn>
                <v-text-field
                  v-model="pesquisa"
                  class="search-input"
                  outline
                  single-line
                  label="Pesquisa"
                  hide-details
                />
              </v-responsive>
            </v-flex>
            <v-container grid-list-sm fluid>
              <v-layout row wrap>
                <v-flex
                  v-show="resultadosFiltrados.length === 0"
                  class="grey--text text-xs-center display-1 py-3"
                >
                  {{
                    pesquisa && pesquisa.length > 0
                      ? 'Nenhum resultado encontrado :('
                      : 'Nenhum arquivo cadastrado'
                  }}
                </v-flex>
                <v-flex
                  v-for="arquivo in resultadosFiltrados"
                  :key="arquivo.id"
                  xs3
                  :sm2="!sidebarAberta"
                  :sm4="sidebarAberta"
                  :md2="!sidebarAberta"
                  :md3="sidebarAberta"
                  :lg1="!sidebarAberta"
                  :lg2="sidebarAberta"
                  xl1
                  :class="{
                    'grid-item': true,
                    'sidebar-aberta': sidebarAberta
                  }"
                >
                  <v-card
                    v-ripple
                    flat
                    tile
                    @click="
                      modoEditar === false && modoCriar === false
                        ? (arquivoSelecionado = arquivo)
                        : ''
                    "
                  >
                    <v-img :src="`api/arquivos/${arquivo.id}`" />
                  </v-card>
                </v-flex>
              </v-layout>
            </v-container>
          </v-flex>
        </v-layout>
      </v-card>
    </v-dialog>
    <v-dialog v-model="confirmarRemoverAberto" max-width="320">
      <v-card>
        <v-card-title class="headline">
          Tem certeza?
        </v-card-title>
        <v-card-text>
          Tem certeza que deseja remover o arquivo "{{
            arquivoSelecionado.nome
          }}"? Essa ação é irreversível.
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn
            color="grey darken-1"
            outline
            @click="confirmarRemoverAberto = false"
          >
            Cancelar
          </v-btn>
          <v-btn color="red lighten-1" dark @click="removerArquivo">
            Remover
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <slot :toggleDialog="toggleDialog" :arquivo="arquivoExposto" />
    <input v-show="false" ref="input" type="file" @change="onInputChange" />
  </div>
</template>

<script>
const arquivoVazio = {
  nome: '',
  alt: '',
  descricao: '',
  caminhoTemporario: ''
}
export default {
  name: 'InputArquivo',
  props: {
    tamanhoMaximoMB: {
      type: Number,
      required: false,
      default: 5
    }
  },
  data() {
    return {
      arquivoVazio,
      dialogPrincipalAberto: false,
      modoEditar: false,
      modoCriar: false,
      arquivos: [],
      arquivoSelecionado: arquivoVazio,
      backupArquivo: {},
      confirmarRemoverAberto: false,
      pesquisa: '',
      arquivoExposto: arquivoVazio
    }
  },
  computed: {
    resultadosFiltrados() {
      return this.arquivos.filter(
        (arquivo) =>
          arquivo.nome.includes(this.pesquisa) ||
          arquivo.descricao.includes(this.pesquisa)
      )
    },
    todosCamposPreenchidos() {
      return (
        this.arquivoSelecionado.nome.length > 0 &&
        this.arquivoSelecionado.alt.length > 0 &&
        this.arquivoSelecionado.descricao.length > 0 &&
        this.arquivoSelecionado.caminhoTemporario !== undefined &&
        this.arquivoSelecionado.caminhoTemporario.length > 0
      )
    },
    sidebarAberta() {
      return (
        this.modoCriar === true ||
        this.modoEditar === true ||
        this.arquivoSelecionado !== arquivoVazio
      )
    }
  },
  mounted() {},
  methods: {
    limparPesquisa() {
      this.pesquisa = ''
    },
    onEscolherAtivado() {
      this.arquivoExposto = this.arquivoSelecionado
      this.arquivoSelecionado = arquivoVazio
      this.toggleDialog()
    },
    abrirImagemEmNovaAba() {
      if (this.arquivoSelecionado === arquivoVazio) return
      window.open(`api/arquivos/${this.arquivoSelecionado.id}`, '_blank')
    },
    toggleDialog() {
      this.dialogPrincipalAberto = !this.dialogPrincipalAberto
      if (this.arquivos.length === 0) {
        this.indexArquivos()
      }
    },
    indexArquivos() {
      this.$nuxt.$loading.start()
      this.$axios
        .get('arquivos')
        .then(({ data }) => {
          this.arquivos = data.dados || []
        })
        .catch((response) => {
          this.arquivos = response
        })
        .finally(() => {
          this.$nuxt.$loading.finish()
        })
    },
    iniciarEdicao() {
      this.backupArquivo = { ...this.arquivoSelecionado }
      this.modoEditar = true
    },
    cancelarEdicao() {
      this.arquivoSelecionado = this.backupArquivo
      this.modoEditar = false
    },
    salvarEdicao() {
      this.$nuxt.$loading.start()
      this.$axios
        .patch(
          `arquivos/${this.arquivoSelecionado.id}`,
          this.arquivoSelecionado
        )
        .then(() => {
          this.$toast.success('Arquivo salvo com sucesso', { duration: 2000 })
          this.modoEditar = false
        })
        .catch((response) => {
          this.$toast.error('Um erro ocorreu ao editar o arquivo', {
            duration: 2000
          })
          console.log(response)
        })
        .finally(() => {
          this.$nuxt.$loading.finish()
        })
    },
    removerArquivo() {
      this.$nuxt.$loading.start()
      this.$axios
        .delete(`arquivos/${this.arquivoSelecionado.id}`)
        .then(() => {
          this.$toast.success('Arquivo removido com sucesso', {
            duration: 2000
          })
          this.modoEditar = false
          this.arquivoSelecionado = arquivoVazio
          this.confirmarRemoverAberto = false
          this.indexArquivos()
        })
        .catch((response) => {
          this.$toast.error('Um erro ocorreu ao remover o arquivo', {
            duration: 2000
          })
          console.log(response)
        })
        .finally(() => {
          this.$nuxt.$loading.finish()
        })
    },
    cancelarCriacao() {
      this.arquivoSelecionado = arquivoVazio
      this.modoCriar = false
    },
    salvarCriacao() {
      this.$nuxt.$loading.start()
      const formData = new FormData()
      formData.append('arquivo', this.arquivoSelecionado.arquivoUploaded)
      formData.append('nome', this.arquivoSelecionado.nome)
      formData.append('alt', this.arquivoSelecionado.alt)
      formData.append('descricao', this.arquivoSelecionado.descricao)
      this.$axios
        .post('arquivos', formData)
        .then(() => {
          this.$toast.success('Arquivo salvo com sucesso', { duration: 2000 })
          this.arquivoSelecionado = arquivoVazio
          this.modoEditar = false
          this.modoCriar = false
          this.indexArquivos()
        })
        .catch((response) => {
          this.$toast.error('Um erro ocorreu ao criar o arquivo', {
            duration: 2000
          })
          console.log(response)
        })
        .finally(() => {
          this.$nuxt.$loading.finish()
        })
    },
    onUploadAtivado() {
      // clica no <input type="file" ref="input"> (o input escondido que vai receber o arquivo)
      this.$refs.input.click()
    },
    onInputChange() {
      const arquivos = this.$refs.input.files
      // se o array de arquivos escolhidos tiver algum arquivo
      if (arquivos.length === 0) {
        return
      }
      // pega a primeira posição do array de arquivos escolhidos
      const arquivoUploaded = arquivos[0]
      // arquivoUploaded.size originalmente está em bytes, essa operação pega o valor em megabytes
      const tamanhoEmMB = arquivoUploaded.size / 1000000
      // se a arquivo for maior que o tamanho máximo
      if (tamanhoEmMB > this.tamanhoMaximoMB) {
        this.$toast.error(
          `A arquivo deve ter no máximo ${this.tamanhoMaximoMB}MB`,
          { duration: 4000 }
        )
        return
      }
      // coloca no v-model o novo valor do arquivo
      this.arquivoSelecionado = {
        ...this.arquivoSelecionado,
        caminhoTemporario: URL.createObjectURL(arquivoUploaded),
        arquivoUploaded
      }
    }
  }
}
</script>

<style lang="stylus" scoped>
.content
  overflow-y auto

.preview-form
  display flex
  align-items center
  flex-direction column
  max-width 350px
  position relative
  overflow-y auto
  border-bottom 2px solid #a3a3a3
  @media(min-width 600px)
    border-bottom none
    border-right 2px solid #c2c2c2
  .top-btns
    position absolute
    right 10px
    top 10px
    z-index 1

.wrapper-grid-fotos
  overflow-y auto

.v-dialog__content >>> .v-dialog--scrollable
  overflow-y hidden

.search-input >>> .v-input__control
  .v-input__slot
    border-radius 30px
  .v-label
    top 16px
    font-size 16px
  input
    margin-top 10px
    font-size 16px
.search >>> .v-responsive__content
  display flex
  align-items center

.preview-img
  width 100%
  max-height 50%
  object-fit contain

.grid-item
  max-height 22vw
  overflow hidden

  .v-card, .v-image
    width 100%
    height 100%

  @media(min-width 600px)
    max-height 16vw
    &.sidebarAberta
      max-height 15vw
  @media(min-width 1264px)
    max-height 8vw
    &.sidebarAberta
      max-height calc(60px + 9vw)
      max-height 15vw
  @media(min-width 1904px)
    max-height calc(60px + 5vw)
    &.sidebarAberta
      max-height calc(40px + 5vw)
</style>
