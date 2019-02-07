import axios from 'axios'
import Vue from 'vue'

export default {

    namespaced: true,

    state: {
        filters: [],
        operators: {
            map: {
                blob: 'text',
                boolean: 'boolean',
                country: 'boolean',
                currency: 'boolean',
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
                payload.available.forEach(function (column) {
                    if (-1 === displayColumns.indexOf(column)) {
                        displayColumns.push(column)
                    }
                })
            }

            if ('remove' === payload.action) {
                payload.display.forEach(function (column) {
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
            Vue.set(state, 'savedSearch', value)
        },
        savedSearches(state, value) {
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
        },
        token(state, value) {
            state.token = value
        }
    },

    actions: {
        getSavedSearch({ commit, state }, id) {

            return axios({
                method: 'get',
                url: '/search/saved-searches/view/' + id,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(response => {
                if (true === response.data.success) {
                    commit('savedSearch', response.data.data)
                }
            }).catch(error => console.log(error))
        },
        getSavedSearches({ commit, state }) {

            return axios({
                method: 'get',
                url: '/search/saved-searches/index',
                params: {
                    model: state.savedSearch.model,
                    system: 0,
                    user_id: state.savedSearch.user_id
                },
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(response => {
                if (true === response.data.success) {
                    commit('savedSearches', response.data.data)
                }
            }).catch(error => console.log(error))
        },
        saveSearch() {
            if ('' === this.saveSearchName) {
                return
            }

            axios({
                method: 'post',
                url: '/search/saved-searches/add',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(response => {
                let data = response.data.success ? response.data.data : null
                console.log(data)
                if (null === data) {
                    return
                }
            }).catch(error => console.log(error))
        },

        search: function ({ commit, state }) {
            return axios({
                method: 'post',
                url: '/api/' + state.savedSearch.model + '/search',
                params: {
                    sort: state.savedSearch.content.saved.sort_by_field,
                    direction: state.savedSearch.content.saved.sort_by_order
                },
                data: state.savedSearch.content.saved,
                headers: {
                    'Authorization': 'Bearer ' + state.token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(response => {
                if (true === response.data.success) {
                    commit('result', { data: response.data.data, pagination: response.data.pagination })
                }
            }).catch(error => console.log(error))
        }
    }

}