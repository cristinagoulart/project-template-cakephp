import axios from 'axios'
import Vue from 'vue'
import { uuid } from 'vue-uuid'

export default {

  namespaced: true,

  state: {
    exportId: '',
    filters: [],
    operators: {
      map: {
        blob: 'text',
        boolean: 'boolean',
        country: 'boolean',
        currency: 'boolean',
        date: 'number',
        datetime: 'number',
        dblist: 'boolean',
        decimal: 'number',
        email: 'text',
        integer: 'number',
        list: 'boolean',
        phone: 'text',
        related: 'boolean',
        reminder: 'number',
        string: 'text',
        sublist: 'boolean',
        text: 'text',
        time: 'number',
        url: 'text'
      },
      types: {
        boolean: [
          { value: 'is', text: 'is' },
          { value: 'is_not', text: 'is not' }
        ],
        number: [
          { value: 'is', text: 'is' },
          { value: 'is_not', text: 'is not' },
          { value: 'greater', text: 'greater' },
          { value: 'less', text: 'less' }
        ],
        text: [
          { value: 'contains', text: 'contains' },
          { value: 'not_contains', text: 'does not contain' },
          { value: 'starts_with', text: 'starts with' },
          { value: 'ends_with', text: 'ends with' }
        ]
      }
    },
    savedSearch: {
      id: '',
      name: '',
      user_id: '',
      model: '',
      content: {
        saved: {
          aggregator: 'AND',
          criteria: {},
          display_columns: [],
          sort_by_field: '',
          sort_by_order: 'asc',
          group_by: ''
        }
      }
    },
    savedSearches: []
  },

  getters: {
    filtersGroup (state) {
      const result = {}
      for (var index in state.filters) {
            const filter = state.filters[index]
        if (! result.hasOwnProperty(filter.group)) {
          result[filter.group] = []
        }
        result[filter.group].push(filter)
      }

      return result
    },

    filtersFlat (state) {
      const result = {}
      for (var index in state.filters) {
        const filter = state.filters[index]
        result[filter.field] = filter
      }

      return result
    }
  },

  mutations: {
    aggregator (state, value) {
      state.savedSearch.content.saved.aggregator = value
    },
    criteriaCreate (state, payload) {
      const filter = state.filters.filter(filter => filter.field === payload.field)

      const guid = uuid.v4()

      const criteria = state.savedSearch.content.saved.criteria
      if (!criteria.hasOwnProperty(filter[0].field)) {
        Vue.set(criteria, filter[0].field, {})
      }

      Vue.set(criteria[filter[0].field], guid, {
        type: filter[0].type,
        operator: state.operators.types[state.operators.map[filter[0].type]][0].text,
        value: payload.value !== '' ? payload.value : (filter[0].type === 'boolean' ? 0 : '')
      })
    },
    criteriaRemove (state, value) {
      const criteria = state.savedSearch.content.saved.criteria

      for (const field in criteria) {
        for (const guid in criteria[field]) {
          if (value !== guid) {
            continue
          }

          Vue.delete(criteria[field], guid)

          if (Object.keys(criteria[field]).length === 0) {
            Vue.delete(criteria, field)
          }
        }
      }
    },
    criteriaOperator (state, payload) {
      Vue.set(state.savedSearch.content.saved.criteria[payload.field][payload.guid], 'operator', payload.value)
    },
    criteriaValue (state, payload) {
      Vue.set(state.savedSearch.content.saved.criteria[payload.field][payload.guid], 'value', payload.value)
    },
    displayColumns (state, payload) {
      if (['add', 'remove'].indexOf(payload.action) === -1) {
        return
      }

      const displayColumns = state.savedSearch.content.saved.display_columns

      if (payload.action === 'add') {
        payload.available.map(function (column) {
          if (displayColumns.indexOf(column) === -1) {
            displayColumns.push(column)
          }
        })
      }

      if (payload.action === 'remove') {
        payload.display.map(function (column) {
          const index = displayColumns.indexOf(column)
          if (index > -1) {
            displayColumns.splice(index, 1)
          }
        })
      }
    },
    displayColumnsSort (state, payload) {
      if (['up', 'down'].indexOf(payload.direction) === -1) {
        return
      }

      const displayColumns = state.savedSearch.content.saved.display_columns
      const selection = 'up' === payload.direction ? payload.columns : payload.columns.reverse()
      let previous = -1
      selection.forEach(function (column) {
        const length = 'up' === payload.direction ? 0 : displayColumns.length - 1
        const index = displayColumns.indexOf(column)
        const start = index + ('up' === payload.direction ? - 1 : 1)

        if (length === index || start === previous) {
          previous = index

          return
        }

        displayColumns.splice(start, 0, displayColumns.splice(index, 1)[0])
      })
    },
    exportId (state, value) {
      state.exportId = value
    },
    filters (state, value) {
      state.filters = value
    },
    groupBy (state, value) {
      state.savedSearch.content.saved.group_by = value
    },
    name(state, value) {
      state.savedSearch.name = value
    },
    result (state, value) {
      state.result = value
    },
    savedSearch (state, value) {
      if (Array.isArray(value.content.saved.criteria) && value.content.saved.criteria.length === 0) {
        value.content.saved.criteria = {}
      }

      if (value.content.saved.criteria === undefined) {
        value.content.saved.criteria = {}
      }

      if (!value.content.saved.hasOwnProperty('group_by')) {
        value.content.saved.group_by = ''
      }

      Vue.set(state, 'savedSearch', value)
    },
    savedSearches (state, value) {
      value.sort((a, b) => (a.name > b.name) ? -1 : 1)
      state.savedSearches = value
    },
    savedSearchId (state, value) {
      state.savedSearch.id = value
    },
    savedSearchModel (state, value) {
      state.savedSearch.model = value
    },
    savedSearchUserId (state, value) {
      state.savedSearch.user_id = value
    },
    sortByField (state, value) {
      state.savedSearch.content.saved.sort_by_field = value
    },
    sortByOrder (state, value) {
      state.savedSearch.content.saved.sort_by_order = value
    }
  },

  actions: {
    savedSearchCopy ({ commit, state, dispatch }, payload) {
      return axios({
        method: 'get',
        url: '/search/saved-searches/view/' + payload.id
      }).then(response => {
        if (response.data.success !== true) {
          return
        }

        const data = response.data.data

        delete data.id
        data.user_id = payload.user_id

        axios({
          method: 'post',
          url: '/search/saved-searches/add',
          data: data
        }).then(response => {
          if (response.data.success === true) {
            dispatch('savedSearchesGet')

            Vue.notify({
              group: 'SearchNotification',
              type: 'info',
              text: 'Successfully copied the search'
            })
          }
        }).catch(error => console.log(error))
      }).catch(error => console.log(error))
    },
    savedSearchDelete ({ commit, state, dispatch }, id) {
      return axios({
        method: 'delete',
        url: '/search/saved-searches/delete/' + id
      }).then(response => {
        if (response.data.success === true) {
          dispatch('savedSearchesGet')
        }
      }).catch(error => console.log(error))
    },
    savedSearchExport ({ commit, state }) {
      const data = state.savedSearch
            // this is treated as temporary saved search
      data.name = ''

      return axios({
        method: 'post',
        url: '/search/saved-searches/add',
        data: data
      }).then(response => {
        if (response.data.success === true) {
          commit('exportId', response.data.data.id)
        }
      }).catch(error => console.log(error))
    },
    savedSearchGet ({ commit, state }, id) {
      return axios({
        method: 'get',
        url: '/search/saved-searches/view/' + id
      }).then(response => {
        if (response.data.success === true) {
          commit('savedSearch', response.data.data)

          Vue.notify({
            group: 'SearchNotification',
            type: 'info',
            text: 'Successfully loaded search results'
          })
        }
      }).catch(error => console.log(error))
    },
    savedSearchSave ({ commit, state, dispatch }) {
      const create = state.savedSearch.id === ''

      return axios({
        method: create ? 'post' : 'put',
        url: '/search/saved-searches/' + (create ? 'add' : 'edit/' + state.savedSearch.id),
        data: state.savedSearch
      }).then(response => {
        if (response.data.success === true) {
          if (create) {
            commit('savedSearchId', response.data.data.id)
          }
          dispatch('savedSearchesGet')
          Vue.notify({
            group: 'SearchNotification',
            type: 'info',
            text: 'Search successfully saved'
          })
        }
      }).catch(error => console.log(error))
    },
    savedSearchesGet ({ commit, state }) {
      return axios({
        method: 'get',
        url: '/search/saved-searches/index',
        params: {
          model: state.savedSearch.model,
          system: 0,
          user_id: state.savedSearch.user_id
        }
      }).then(response => {
        if (response.data.success === true) {
          commit('savedSearches', response.data.data)
        }
      }).catch(error => console.log(error))
    }
  }

}
