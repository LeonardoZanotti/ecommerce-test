<template>
  <v-layout class="hero column justify-center text-center">
    <div class="background">
      <div class="content">
        <div class="font-weight-bold white--text titulo">
          <p class="text-center margin-text">{{ titulo }}</p>
        </div>
      </div>
    </div>
  </v-layout>
</template>

<script>
export default {
  name: 'BannerProdutos',
  data() {
    return {
      titulo: 'Nossos Produtos'
    }
  }
}
</script>

<style lang="stylus" scoped>
  // Variaveis tamanhos de tela do Vuetify
  sm = 600px
  md = 960px
  lg = 1264px
  xl = 1904px

  alturaNavbarXS = 64px
  alturaNavbarSM = 80px
  alturaNavbarMD = 80px
  alturaNavbarLG = 110px
  alturaNavbarXL = 110px

  .margin-text
    margin-top 200px
  .hero
    position relative
    min-height 60vh
    padding alturaNavbarXS 5vw
    background-image url(/imagens/e-commerce.jpg)
    background-attachment scroll
    background-repeat no-repeat
    background-size cover
    background-position: center
    .background {
        position absolute
        z-index 0
        width 100%
        height 100%
        left 0
        background-color rgba(0,0,0,0.3)
    }
    .content {
        position relative
        height auto
        text-align center
    }
    .titulo
      z-index 1
      font-size 2rem

@media (min-width: sm)
    .hero
        padding alturaNavbarSM 5vw
        .titulo
            font-size 2rem
        .subtitulo
            font-size 1.0rem

@media (min-width: md)
    .hero
        padding alturaNavbarMD 5vw
        .titulo
            font-size 2.5rem

@media (min-width: lg)
    .hero
        padding alturaNavbarLG 10vw
        .titulo
            font-size 3.5rem
    .post-item
        padding alturaNavbarXS 10vw

@media (min-width: xl)
    .hero
        padding alturaNavbar XL 10vw
        .titulo
            font-size 4.5rem

@keyframes float
    0%
      transform translateY(25px) rotate(1deg)
    50%
      transform translateY(5px) rotate(0deg)
    100%
      transform translateY(25px) rotate(1deg)
</style>
