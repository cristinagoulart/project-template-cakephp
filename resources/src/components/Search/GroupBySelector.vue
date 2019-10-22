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
        <select v-model="groupBy" class="form-control input-sm">
          <option value="">-- Group by --</option>
          <option v-for="item in fieldsList" v-if="item.group === model" :value="item.field">{{ item.label }}</option>
        </select>
      </div>
    </div>
  </div>
</template>
<script>
import Aggregate from '@/utils/aggregate'
import { mapGetters, mapState } from 'vuex'

export default {
  name: 'GroupBySelector',
  data() {
    return {
      model: this.$store.state.search.model
    }
  },
  computed: {
    ...mapGetters({
      models: 'search/displayableModels'
    }),
    ...mapState({
      fields: state => state.search.fields,
      fieldsList: state => state.search.filters
    }),
    groupBy: {
      get() {
        return this.$store.state.search.group_by
      },
      set(value) {
        this.$store.commit('search/groupBy', value)

        let fields = []
        if (value) {
          fields.push(value)
        }

        const aggregate = this.fields.find(item => Aggregate.isAggregate(item))
        if (aggregate !== undefined) {
          fields.push(aggregate)
        }

        this.$store.commit('search/fields', fields)
      }
    }
  },
  watch: {
    groupBy(value) {
      this.model = value ?
        this.fieldsList.find(item => item.field === this.groupBy).group :
        this.$store.state.search.model
    }
  }
}
</script>
