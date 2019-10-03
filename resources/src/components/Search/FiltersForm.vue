<template>
  <div>
    <template v-for="(field_criteria, field) in criteria" v-if="!isAggregate(field)">
      <div v-for="(info, guid) in field_criteria" class="form-group">
        <div class="row">
          <div class="col-xs-12 col-md-4 col-lg-3">
            <label>{{ filters[field].label }}
              <template v-if="filters[field].group !== model"><i class="fa fa-info-circle" :title="filters[field].group"></i></template>
            </label>
          </div>
          <div class="col-xs-4 col-md-2 col-lg-2">
            <select v-model="filtersList[guid]" class="form-control input-sm" @change="filterUpdated(field, guid, $event.target.value)">
              <option v-for="item in getFiltersByField(field)" :value="item.value">{{ item.text }}</option>
            </select>
          </div>
          <div class="col-xs-6 col-md-5">
            <component
              :is="info.type + 'Input'"
              :guid="guid"
              :field="field"
              :value="info.value"
              :options="filters[field].options"
              :source="filters[field].source"
              :display-field="filters[field].display_field"
              :multiple="true"
              @input-value-updated="valueUpdated"
            />
          </div>
          <div class="col-sm-2 col-md-1">
            <button type="button" @click="remove(guid)" class="btn btn-default btn-xs"><i class="fa fa-trash" aria-hidden="true"></i></button>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
<script>
import Aggregate from '@/utils/aggregate'
import inputs from '@/components/fh'
import { mapGetters, mapState } from 'vuex'
import { FIELD_OPERATOR_TYPES, FIELD_TYPE_MAP } from '@/utils/search'

export default {
  name: 'FilterForm',
  components: inputs,
  computed: {
    ...mapGetters({
      filters: 'search/filtersFlat'
    }),
    ...mapState({
      criteria: state => state.search.criteria,
      model: state => state.search.model
    }),
    filtersList() {
      let result = {}
      for (const field in this.criteria) {
        for (const guid in this.criteria[field]) {
          result[guid] = this.criteria[field][guid].operator
        }
      }

      return result
    }
  },
  methods: {
    filterUpdated(field, guid, filter) {
      this.$store.commit('search/criteriaOperator', { field: field, guid: guid, operator: filter })
    },
    getFiltersByField(field) {
      return FIELD_OPERATOR_TYPES[FIELD_TYPE_MAP[this.filters[field].type]]
    },
    isAggregate(field) {
      return Aggregate.isAggregate(field)
    },
    remove(guid) {
      this.$store.commit('search/criteriaRemove', guid)
    },
    valueUpdated(field, guid, value) {
      this.$store.commit('search/criteriaValue', { field: field, guid: guid, value: value })
    }
  }
}
</script>
