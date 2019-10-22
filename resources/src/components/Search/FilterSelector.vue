<template>
  <div>
    <div class="col-xs-4">
      <div class="form-group">
        <select v-model="model" class="form-control input-sm">
          <option v-for="item in models" :value="item">{{ item }}</option>
        </select>
      </div>
    </div>
    <div class="col-xs-8">
      <div class="form-group">
        <select v-model="field" class="form-control input-sm" @change="create()">
          <option value="">-- Add filter --</option>
          <option v-for="item in fields" v-if="item.group === model" :value="item.field">{{ item.label }}</option>
        </select>
      </div>
    </div>
  </div>
</template>
<script>
import { mapGetters, mapState } from 'vuex'

/**
 * In the future this can be moved as part of the TableAjax.vue component.
 */

export default {
  name: 'FilterSelector',
  computed: {
    ...mapGetters({
      models: 'search/filterModels'
    }),
    ...mapState({
      fields: state => state.search.filters
    })
  },
  data() {
    return {
      field: '',
      model: this.$store.state.search.model
    }
  },
  methods: {
    create() {
      this.$store.commit('search/criteriaCreate', { field: this.field })
      this.field = ''
    }
  }
}
</script>
