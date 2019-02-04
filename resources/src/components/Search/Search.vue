<template>
    <div>
        <div class="box box-primary">
            <div class="box-body">
                <form class="search-form" novalidate="novalidate" v-on:submit.prevent="search">
                    <div class="row">
                        <div class="col-lg-3 col-lg-push-9">
                            <div class="form-group">
                                <select v-model="filter" class="form-control input-sm" v-on:change="criteriaCreate()">>
                                    <option value="">-- Add filter --</option>
                                    <template v-for="(group_filters, group) in filtersGroup">
                                        <optgroup :label="group">
                                            <option v-for="filter in group_filters" :value="filter.field">{{ filter.label }}</option>
                                        </optgroup>
                                    </template>
                                </select>
                            </div>
                            <div class="form-group">
                                <select v-model="aggregator" class="form-control input-sm">
                                    <option v-for="aggregator in aggregators" :value="aggregator.value">{{ aggregator.text }}</option>
                                </select>
                            </div>
                        </div>
                        <hr class="visible-xs visible-sm visible-md" />
                        <div class="col-lg-9 col-lg-pull-3">
                            <fieldset>
                                <template v-for="(fields, fieldName) in criteria">
                                    <div v-for="(field, guid) in fields" class="form-group">
                                        <div class="row">
                                            <div class="col-xs-12 col-md-3 col-lg-2"><label>{{ filtersFlat[fieldName].label }} {{ guid }}</label></div>
                                            <div class="col-xs-4 col-md-2 col-lg-3">
                                                <component :is="field.type + 'Operator'" :guid="guid" :field="fieldName" />
                                            </div>
                                            <div class="col-xs-6 col-md-5 col-lg-4">
                                                <component :is="field.type + 'Input'" :guid="guid" :field="fieldName" :value="field.value" :options="filtersFlat[fieldName].options" :source="filtersFlat[fieldName].source" :url="filtersFlat[fieldName].url" @value-changed="valueChanged" />
                                            </div>
                                            <div class="col-xs-2">
                                                <div class="input-sm">
                                                    <button type="button" @click="criteriaRemove(guid)" class="btn btn-default btn-xs"><i class="fa fa-trash" aria-hidden="true"></i></button>
                                                    <button type="button" @click="criteriaCopy(guid)" class="btn btn-default btn-xs"><i class="fa fa-clone" aria-hidden="true"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </fieldset>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8 col-lg-9">
                            <div class="row">
                                    <div class="col-md-5 col-lg-4">
                                        <label for="available-columns">Available Columns</label>
                                        <select v-model="selectedColumns.available" class="form-control input-sm" multiple size="8">
                                            <option v-for="filter in filtersList" v-if="-1 === displayColumns.indexOf(filter.field)" :value="filter.field">
                                                {{ filter.label }}
                                                <template v-if="filter.group !== model">({{ filter.group }})</template>
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label>&nbsp;</label>
                                        <button type="button" @click="displayColumnsUpdated('add')" class="btn btn-block btn-xs">
                                            <i class="glyphicon glyphicon-chevron-right"></i>
                                        </button>
                                        <button type="button" @click="displayColumnsUpdated('remove')" class="btn btn-block btn-xs">
                                            <i class="glyphicon glyphicon-chevron-left"></i>
                                        </button>
                                    </div>
                                    <div class="col-md-5 col-lg-4">
                                        <label for="display-columns">Display Columns</label>
                                        <select v-model="selectedColumns.display" class="form-control input-sm" multiple size="8">
                                            <option v-for="column in displayColumns" :value="filtersFlat[column].field">
                                                {{ filtersFlat[column].label }}
                                                <template v-if="filtersFlat[column].group !== model">({{ filtersFlat[column].group }})</template>
                                            </option>
                                        </select>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <button type="button" @click="displayColumnsSorted('up')" :disabled="0 === displayColumns.length" class="btn btn-block btn-xs">
                                                    <i class="glyphicon glyphicon-arrow-up"></i>
                                                </button>
                                            </div>
                                            <div class="col-sm-6">
                                                <button type="button" @click="displayColumnsSorted('down')" :disabled="0 === displayColumns.length" class="btn btn-block btn-xs">
                                                    <i class="glyphicon glyphicon-arrow-down"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <div class="col-lg-2">
                                    <div class="row">
                                        <div class="col-md-4 col-lg-12">
                                            <div class="form-group">
                                                <label for="sort-field">Sort Field</label>
                                                <select v-model="sortByField" class="form-control input-sm">
                                                    <template v-for="(group_filters, group) in filtersGroup">
                                                        <optgroup :label="group">
                                                            <option v-for="filter in group_filters" :value="filter.field">{{ filter.label }}</option>
                                                        </optgroup>
                                                    </template>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-lg-12">
                                            <div class="form-group">
                                                <label for="sort-order">Sort Order</label>
                                                <select v-model="sortByOrder" class="form-control input-sm">
                                                    <option v-for="order in sortByOrders" :value="order.value">{{ order.text }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-lg-12">
                                            <div class="form-group">
                                                <label for="group-by">Group By</label>
                                                <select v-model="groupBy" class="form-control input-sm">
                                                    <option value="">-- Please choose --</option>
                                                    <option v-for="filter in filtersList" :value="filter.field" v-if="filter.group === model">{{ filter.label }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary btn-sm" type="submit"><i class="fa fa-search"></i> Search</button>
                        </div>
                        <div class="col-md-4 col-lg-3">
                            <div class="form-group">
                                <label class="control-label" for="saved-searches">Saved Searches</label>
                                <div class="input-group">
                                    <select v-model="savedSearchSelected" class="form-control input-sm">
                                        <option v-for="savedSearch in savedSearches" :value="savedSearch.id">{{ savedSearch.name }}</option>
                                    </select>
                                    <span class="input-group-btn">
                                        <button type="button" @click="savedSearchGet()" :disabled="'' === savedSearchSelected" class="btn btn-default btn-sm">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        <button type="button" :disabled="'' === savedSearchSelected" class="btn btn-default btn-sm">
                                            <i class="fa fa-clone"></i>
                                        </button>
                                        <button type="button" :disabled="'' === savedSearchSelected" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label" for="save-search">Save Search</label>
                                <div class="input-group">
                                    <div class="form-group input text required">
                                        <input type="text" v-model="name" class="form-control input-sm" placeholder="Saved search name" required="required">
                                    </div>
                                    <span class="input-group-btn">
                                        <button type="button" @click="saveSearch()" class="btn btn-sm btn-primary"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title"><a href="#">{{ model }}</a></h3>
            </div>
            <div class="box-body">
                <search-table v-if="loadResult" :ajax="tableAjax" :order="[displayColumns.indexOf(sortByField), sortByOrder]" :batch="tableBatch" :with-actions="!(!!+groupBy)" :headers="tableHeaders"></search-table>
            </div>
        </div>
    </div>
</template>

<script>
import searchTable from '@/components/ui/Table.vue'
import inputs from '@/components/fh'
import operators from '@/components/Search/Operators'
import axios from 'axios'

export default {

    components: Object.assign({ searchTable }, inputs, operators),

    props: {
        batch: {
            type: String,
            required: true
        },
        filters: {
            type: String,
            required: true
        },
        model: {
            type: String,
            required: true
        },
        id: {
            type: String,
            default: ''
        },
        token: {
            type: String,
            required: true
        },
        userId: {
            type: String,
            required: true
        },
        withForm: {
            type: Boolean,
            default: true
        }
    },

    data: function () {
        return {
            aggregators: [
                { text: 'Match all filters', value: 'AND' },
                { text: 'Match any filter', value: 'OR' }
            ],
            filter: '',
            loadResult: false,
            savedSearchSelected: this.$store.state.search.savedSearch.id,
            selectedColumns: {
                available: [],
                display: []
            },
            sortByOrders: [
                { text: 'Ascending', value: 'asc' },
                { text: 'Descending', value: 'desc' }
            ],
            tableBatch: Object.assign(JSON.parse(this.batch), {
                urls: {
                    delete: '/' + this.$store.state.search.savedSearch.model + '/batch/delete',
                    edit: '/' + this.$store.state.search.savedSearch.model + '/batch/edit'
                }
            })
        }
    },

    computed: {
        aggregator: {
            get () {
                return this.$store.state.search.savedSearch.content.saved.aggregator
            },
            set (value) {
                this.$store.commit('search/aggregator', value)
            }
        },
        criteria () {
            return this.$store.state.search.savedSearch.content.saved.criteria
        },
        displayColumns: {
            get () {
                return this.$store.state.search.savedSearch.content.saved.display_columns
            },
            set (value) {
                this.$store.commit('search/displayColumns', value)
            }
        },
        filtersGroup () {
            return this.$store.getters['search/filtersGroup']
        },
        filtersFlat () {
            return this.$store.getters['search/filtersFlat']
        },
        filtersList () {
            return this.$store.state.search.filters
        },
        groupBy: {
            get () {
                return this.$store.state.search.savedSearch.content.saved.group_by
            },
            set (value) {
                this.$store.commit('search/groupBy', value)
            }
        },
        name: {
            get () {
                return this.$store.state.search.savedSearch.name
            },
            set (value) {
                this.$store.commit('search/name', value)
            }
        },
        // model () {
        //     return this.$store.state.search.savedSearch.model
        // },
        // savedSearch () {
        //     const id = this.$store.state.search.savedSearch.id

        //     return id ? id : (0 < this.savedSearches.length ? this.savedSearches[0].id : '')
        // },
        savedSearches () {
            return this.$store.state.search.savedSearches
        },
        // savedSearchId () {
        //     get () {
        //         const id = this.$store.state.search.savedSearch.id

        //         return id ? id : (0 < this.savedSearches.length ? this.savedSearches[0].id : '')
        //     }//,
        //     // set (value) {
        //     //     this.$store.commit('search/savedSearchId', value)
        //     // }
        // },
        // searchResult () {
            // return this.$store.state.search.result
        // },
        sortByField: {
            get () {
                return this.$store.state.search.savedSearch.content.saved.sort_by_field
            },
            set (value) {
                this.$store.commit('search/sortByField', value)
            }
        },
        sortByOrder: {
            get () {
                return this.$store.state.search.savedSearch.content.saved.sort_by_order
            },
            set (value) {
                this.$store.commit('search/sortByOrder', value)
            }
        },
        tableAjax() {
            return {
                url: '/api/' + this.$store.state.search.savedSearch.model + '/search',
                type: 'POST',
                extras: {
                    criteria: this.criteria
                },
                headers: {
                    'Authorization': 'Bearer ' + this.token
                }
            }
        },
        tableHeaders () {
            const self = this

            const result = []
            this.displayColumns.forEach(function (column) {
                result.push({ value: column, text: self.filtersFlat[column].label })
            })

            return result
        }
    },

    created: function () {
        this.$store.commit('search/filters', JSON.parse(this.filters))
        this.$store.commit('search/token', this.token)

        if ('' !== this.id) {
            this.$store.commit('search/savedSearchId', this.id)
        }

        if ('' === this.id) {
            this.$store.commit('search/savedSearchModel', this.model)
            this.$store.commit('search/savedSearchUserId', this.userId)
        }

        if ('' !== this.id) {
            this.$store.dispatch('search/getSavedSearch', this.id).then(() => {
                this.loadResult = true
            })
        }

        this.$store.dispatch('search/getSavedSearches')
    },

    methods: {
        criteriaCreate () {
            if ('' !== this.filter) {
                this.$store.commit('search/criteriaCreate', this.filter)
            }

            this.filter = ''
        },
        criteriaCopy (guid) {
            this.$store.commit('search/criteriaCopy', guid)
        },
        criteriaRemove (guid) {
            this.$store.commit('search/criteriaRemove', guid)
        },
        // criteriaUpdated (data) {
        //     this.criteria = data
        // },
        displayColumnsUpdated (action) {
            const payload = Object.assign({}, this.selectedColumns, { action: action })

            this.$store.commit('search/displayColumns', payload)
        },
        displayColumnsSorted (direction) {
            const payload = Object.assign({}, { columns: this.selectedColumns.display }, { direction: direction })

            this.$store.commit('search/displayColumnsSort', payload)
        },
        savedSearchGet () {
            this.$store.dispatch('search/getSavedSearch', this.savedSearchSelected).then(() => {
                this.loadResult = true
            })
        },
        // search: function () {
            // this.$store.dispatch('search/search')
        // },
        valueChanged (field, guid, value) {
            this.$store.commit('search/criteriaValue', { field: field, guid: guid, value: value })
        }
    }

}
</script>
<style>
.search-form .form-group {
    margin-bottom: 10px;
}
</style>