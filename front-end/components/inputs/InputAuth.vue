<template>
  <v-text-field
    class="tf"
    v-bind="$attrs"
    hide-details
    outline
    flat
    v-on="$listeners"
  />
</template>
<script>
export default {
  name: 'InputAuth'
}
</script>
<style lang="stylus" scoped>
.tf.theme--light.v-text-field--outline >>> .v-input__control > .v-input__slot
    min-height 0
    border-color transparent !important
    background-color rgba(161, 180, 198, 0.47) !important
    margin 5px 0
    & input
        margin 4px 0
        color #666
</style>
