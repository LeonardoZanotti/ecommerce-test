<template>
  <v-container>
    <v-layout>
      <v-flex>
        <span class="font-weight-thin display-2">TRANSAÇÕES</span>
      </v-flex>
    </v-layout>
    <v-divider color="grey" />
    <v-layout class="mt-4">
      <v-flex>
        <v-data-table
          :headers="headers"
          :items="comprasInfo"
          :search="search"
          class="elevation-3 mt-10"
        >
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
          text: 'Status',
          align: 'left',
          sortable: true,
          value: 'status'
        },
        { text: 'Data', sortable: true, value: 'data' },
        { text: 'Valor', sortable: true, value: 'valor' },
        { text: '', value: 'action', sortable: false }
      ],
      compras: []
    }
  },
  computed: {
    comprasInfo() {
      return this.compras.map((e) => ({
        id: e.id,
        data: e.data,
        valor: e.valor,
        status: e.status
      }))
    }
  },
  async asyncData({ $axios }) {
    const [comprasRes] = await Promise.all([$axios.get('/indexCompras')])
    return {
      compras: comprasRes.data.dados
    }
  },
  methods: {}
}
</script>

<style></style>
