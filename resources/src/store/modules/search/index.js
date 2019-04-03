import axios from 'axios'
import Vue from 'vue'

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
        filtersGroup(state) {
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

        filtersFlat(state) {
            const result = {}
            for (var index in state.filters) {
                const filter = state.filters[index]
                result[filter.field] = filter
            }

            return result
        }
    },

    mutations: {
        aggregator(state, value) {
            state.savedSearch.content.saved.aggregator = value
        },
        criteriaCreate(state, value) {
            const filter = state.filters.filter(filter => filter.field === value)

            var s4 = function () {
                return Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1)
            }

            // const guid = Math.round(1000000 * Math.random())
            const guid = s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4()

            let criteria = state.savedSearch.content.saved.criteria
            if (! criteria.hasOwnProperty(filter[0].field)) {
                Vue.set(criteria, filter[0].field, {})
            }

            Vue.set(criteria[filter[0].field], guid, {
                type: filter[0].type,
                operator: state.operators.types[state.operators.map[filter[0].type]][0].text,
                value: 'boolean' === filter[0].type ? 0 : ''
            })
        },
        criteriaCopy(state, value) {
            var s4 = function () {
                return Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1)
            }

            const newGuid = s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4()
            let criteria = state.savedSearch.content.saved.criteria

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
        criteriaRemove(state, value) {
            let criteria = state.savedSearch.content.saved.criteria

            for (const field in criteria) {
                for (const guid in criteria[field]) {
                    if (value !== guid) {
                        continue
                    }

                    Vue.delete(criteria[field], guid)

                    if (0 === Object.keys(criteria[field]).length) {
                        Vue.delete(criteria, field)
                    }
                }
            }
        },
        criteriaOperator(state, payload) {
            Vue.set(state.savedSearch.content.saved.criteria[payload.field][payload.guid], 'operator', payload.value)
        },
        criteriaValue(state, payload) {
            Vue.set(state.savedSearch.content.saved.criteria[payload.field][payload.guid], 'value', payload.value)
        },
        displayColumns(state, payload) {
            if (-1 === ['add', 'remove'].indexOf(payload.action)) {
                return
            }

            const displayColumns = state.savedSearch.content.saved.display_columns

            if ('add' === payload.action) {
                payload.available.map(function (column) {
                    if (-1 === displayColumns.indexOf(column)) {
                        displayColumns.push(column)
                    }
                })
            }

            if ('remove' === payload.action) {
                payload.display.map(function (column) {
                    const index = displayColumns.indexOf(column)
                    if (-1 < index) {
                        displayColumns.splice(index, 1)
                    }
                })
            }
        },
        displayColumnsSort(state, payload) {
            if (-1 === ['up', 'down'].indexOf(payload.direction)) {
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
        exportId(state, value) {
            state.exportId = value
        },
        filters(state, value) {
            state.filters = value
        },
        groupBy(state, value) {
            state.savedSearch.content.saved.group_by = value
        },
        name(state, value) {
            state.savedSearch.name = value
        },
        result(state, value) {
            state.result = value
        },
        savedSearch(state, value) {
            if (Array.isArray(value.content.saved.criteria) && 0 === value.content.saved.criteria.length) {
                value.content.saved.criteria = {}
            }

            Vue.set(state, 'savedSearch', value)
        },
        savedSearches(state, value) {
            value.sort((a, b) => (a.name > b.name) ? -1 : 1)
            state.savedSearches = value
        },
        savedSearchId(state, value) {
            state.savedSearch.id = value
        },
        savedSearchModel(state, value) {
            state.savedSearch.model = value
        },
        savedSearchUserId(state, value) {
            state.savedSearch.user_id = value
        },
        sortByField(state, value) {
            state.savedSearch.content.saved.sort_by_field = value
        },
        sortByOrder(state, value) {
            state.savedSearch.content.saved.sort_by_order = value
        }
    },

    actions: {
        savedSearchCopy({ commit, state, dispatch }, payload) {

            return axios({
                method: 'get',
                url: '/search/saved-searches/view/' + payload.id,
            }).then(response => {
                if (true !== response.data.success) {
                    return
                }

                const data = response.data.data

                delete data.id
                data.user_id = payload.user_id

                axios({
                    method: 'post',
                    url: '/search/saved-searches/add',
                    data: data,
                }).then(response => {
                    if (true === response.data.success) {
                        dispatch('savedSearchesGet')
                    }
                }).catch(error => console.log(error))


            }).catch(error => console.log(error))
        },
        savedSearchDelete({ commit, state, dispatch }, id) {

            return axios({
                method: 'delete',
                url: '/search/saved-searches/delete/' + id,
            }).then(response => {
                if (true === response.data.success) {
                    dispatch('savedSearchesGet')
                }
            }).catch(error => console.log(error))
        },
        savedSearchExport({ commit, state }) {
            let data = state.savedSearch
            // this is treated as temporary saved search
            data.name = ''

            return axios({
                method: 'post',
                url: '/search/saved-searches/add',
                data: data,
            }).then(response => {
                if (true === response.data.success) {
                    commit('exportId', response.data.data.id)
                }
            }).catch(error => console.log(error))
        },
        savedSearchGet({ commit, state }, id) {

            return axios({
                method: 'get',
                url: '/search/saved-searches/view/' + id,
            }).then(response => {
                if (true === response.data.success) {
                    commit('savedSearch', response.data.data)
                }
            }).catch(error => console.log(error))
        },
        savedSearchSave({ commit, state, dispatch }) {
            const create = '' === state.savedSearch.id

            return axios({
                method: create ? 'post' : 'put',
                url: '/search/saved-searches/' + (create ? 'add' : 'edit/' + state.savedSearch.id),
                data: state.savedSearch,
            }).then(response => {
                if (true === response.data.success) {
                    if (create) {
                        commit('savedSearchId', response.data.data.id)
                    }
                    dispatch('savedSearchesGet')
                }
            }).catch(error => console.log(error))
        },
        savedSearchesGet({ commit, state }) {

            return axios({
                method: 'get',
                url: '/search/saved-searches/index',
                params: {
                    model: state.savedSearch.model,
                    system: 0,
                    user_id: state.savedSearch.user_id
                },
            }).then(response => {
                if (true === response.data.success) {
                    commit('savedSearches', response.data.data)
                }
            }).catch(error => console.log(error))
        }
    }

}