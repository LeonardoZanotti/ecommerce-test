<template>
  <div>
    <v-responsive width="50">
      <img
        src="~/static/imagens/logo.png"
        alt="Logo da Ecomp"
        class="logo disable-select"
      />
    </v-responsive>
    <div class="display-2 pb-4 mt-2 font-weight-bold grey--text text--darken-1">
      {{ titulo }}
    </div>
  </div>
</template>
<script>
export default {
  name: 'TitleAuth',
  props: {
    titulo: {
      type: String,
      required: true,
      default: ''
    }
  }
}
</script>
<style lang="stylus" scoped>
.logo
    width 100%
    height 100%
    max-width 400px
    filter grayscale(100%) brightness(130%) contrast(0)
</style>
