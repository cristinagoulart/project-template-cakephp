import ApiSearch from '@/ApiService/ApiSearch'
import Vue from 'vue'
import { uuid } from 'vue-uuid'
import {
  API_STORE_SEARCH,
  API_VIEW_SEARCH,
  API_LIST_SEARCHES,
  API_EDIT_SEARCH,
  API_DELETE_SEARCH,
  FIELD_TYPE_MAP,
  FIELD_OPERATOR_TYPES,
  SEARCH_INSTANCE
} from '@/utils/search'

export default {
  namespaced: true,

  state: {
    exportId: '',
    filters: [],
    operators: {
      map: FIELD_TYPE_MAP,
      types: FIELD_OPERATOR_TYPES
    },
    savedSearch: SEARCH_INSTANCE,
    savedSearches: []
  },

  getters: {
    filtersGroup (state) {
      const result = {}
      for (var index in state.filters) {
        const filter = state.filters[index]
        if (!result.hasOwnProperty(filter.group)) {
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
        operator: payload.operator !== '' ? payload.operator : state.operators.types[state.operators.map[filter[0].type]][0].text,
        value: payload.value !== '' ? payload.value : (filter[0].type === 'boolean' ? 0 : '')
      })
    },
    criteriaCopy (state, value) {
      const newGuid = uuid.v4()
      const criteria = state.savedSearch.content.saved.criteria

      for (const field in criteria) {
        for (const guid in criteria[field]) {
          if (value !== guid) {
            continue
          }

          const data = Object.assign({}, criteria[field][guid])

          Vue.set(criteria[field], newGuid, data)
        }
      }
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
    criteriaValue (state, { field, guid, value }) {
      Vue.set(state.savedSearch.content.saved.criteria[field][guid], 'value', value)
    },
    displayColumns (state, payload) {
      if (['add', 'remove'].indexOf(payload.action) === -1) {
        return
      }

      const displayColumns = state.savedSearch.content.saved.display_columns

      if (payload.action === 'add') {
        payload.available.map(function (column) {
          const found = state.filters.find(filter => filter.field === column)
          if (displayColumns.indexOf(column) === -1 && found !== undefined) {
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
      return ApiSearch
        .getSearch(API_VIEW_SEARCH, payload.id)
        .then(response => {
          const data = response.data.data
          delete data.id
          data.user_id = payload.user_id

          ApiSearch
            .addSearch(API_STORE_SEARCH, data)
            .then(resp => {
              dispatch('savedSearchesGet')
              dispatch('setNotification', {
                'type': 'info',
                'msg': 'Successfully copied the search'
              })
            })
        })
    },
    savedSearchDelete ({ commit, state, dispatch }, id) {
      return ApiSearch
        .deleteSearch(`${API_DELETE_SEARCH}/${id}`)
        .then(response => {
          dispatch('savedSearchesGet')
          dispatch('setNotification', {
            'type': 'info',
            'msg': 'Saved Search successfully removed'
          })
        })
    },
    savedSearchExport ({ commit, state }) {
      const data = state.savedSearch
      // this is treated as temporary saved search
      data.name = ''

      return ApiSearch
        .exportSearch(API_STORE_SEARCH, data)
        .then(response => {
          commit('exportId', response.data.data.id)
        })
    },
    savedSearchGet ({ commit, state, dispatch }, id) {
      return ApiSearch
        .getSearch(API_VIEW_SEARCH, id)
        .then(response => {
          commit('savedSearch', response.data.data)
          dispatch('setNotification', {
            'type': 'info',
            'msg': 'Successfully loaded search results'
          })
        })
    },
    savedSearchSave ({ commit, state, dispatch }) {
      const create = state.savedSearch.id === ''
      let url = API_STORE_SEARCH

      if (!create) {
        url = `${API_EDIT_SEARCH}/${state.savedSearch.id}`
      }

      if (create) {
        return ApiSearch
          .addSearch(url, state.savedSearch)
          .then(response => {
            commit('savedSearchId', response.data.data.id)
            dispatch('savedSearchesGet')
            dispatch('setNotification', {
              'type': 'info',
              'msg': 'Search successfully saved'
            })
          })
      } else {
        return ApiSearch
          .editSearch(url, state.savedSearch)
          .then(response => {
            commit('savedSearchId', response.data.data.id)
            dispatch('savedSearchesGet')
            dispatch('setNotification', {
              'type': 'info',
              'msg': 'Search successfully saved'
            })
          })
      }
    },
    savedSearchesGet ({ commit, state }) {
      return ApiSearch
        .getSearches(API_LIST_SEARCHES, {
          'model': state.savedSearch.model,
          'system': 0,
          'user_id': state.savedSearch.user_id
        })
        .then(response => {
          commit('savedSearches', response.data.data)
        })
    },
    setNotification ({ commit, state }, data) {
      Vue.notify({
        'group': 'SearchNotification',
        'type': data.type,
        'text': data.msg
      })
    }
  }
}
