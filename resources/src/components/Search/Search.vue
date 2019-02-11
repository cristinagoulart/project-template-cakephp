<template>
    <div>
        <div class="box box-primary">
            <div class="box-body">
                <form class="search-form" novalidate="novalidate" v-on:submit.prevent="search">
                    <div class="row">
                        <div class="col-lg-3 col-lg-push-9">
                            <div class="form-group">
                                <select v-model="filter" class="form-control input-sm" v-on:change="criteriaCreate()">
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
                                <template v-for="(fields, field_name) in criteria">
                                    <div v-for="(field, guid) in fields" class="form-group">
                                        <div class="row">
                                            <div class="col-xs-12 col-md-3 col-lg-2"><label>{{ filtersFlat[field_name].label }}</label></div>
                                            <div class="col-xs-4 col-md-2 col-lg-3">
                                                <select v-model="operator[guid]" class="form-control input-sm" v-on:change="operatorUpdated(field_name, guid, operator[guid])">
                                                    <option v-for="option in $store.state.search.operators.types[$store.state.search.operators.map[filtersFlat[field_name].type]]" v-bind:value="option.value">
                                                        {{ option.text }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-xs-6 col-md-5 col-lg-4">
                                                <component :is="field.type + 'Input'" :guid="guid" :field="field_name" :key="guid + field.value" :value="field.value" :options="filtersFlat[field_name].options" :source="filtersFlat[field_name].source" :url="filtersFlat[field_name].url" :multiple="true" @input-value-updated="criteriaUpdated" />
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
                                        <option value="">-- Please choose --</option>
                                        <option v-for="savedSearch in savedSearches" :value="savedSearch.id">{{ savedSearch.name }}</option>
                                    </select>
                                    <span class="input-group-btn">
                                        <button type="button" @click="savedSearchGet()" :disabled="'' === savedSearchSelected" class="btn btn-default btn-sm">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        <button type="button" @click="savedSearchCopy()" :disabled="'' === savedSearchSelected" class="btn btn-default btn-sm">
                                            <i class="fa fa-clone"></i>
                                        </button>
                                        <button type="button" @click="savedSearchDelete()" :disabled="'' === savedSearchSelected || savedSearchSelected === $store.state.search.savedSearch.id" class="btn btn-danger btn-sm">
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
                                        <button type="button" @click="savedSearchCreate()" class="btn btn-sm btn-primary"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
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
                <search-table v-if="loadResult" :url="'/api/' + $store.state.search.savedSearch.model + '/search'" :token="this.token" request-type="POST" :data="{ criteria: criteria }" :order-field="sortByField" :order-direction="sortByOrder" :model="$store.state.search.savedSearch.model" :batch="{ enabled: batch, field: primaryKey }" :with-actions="!(!!+groupBy)" :headers="tableHeaders" @sort-field-updated="sortFieldUpdated" @sort-order-updated="sortOrderUpdated"></search-table>
            </div>
        </div>
    </div>
</template>

<script>
import searchTable from '@/components/ui/TableAjax.vue'
import inputs from '@/components/fh'
import axios from 'axios'

export default {

    components: Object.assign({ searchTable }, inputs),

    props: {
        batch: {
            type: Boolean,
            default: false
        },
        displayFields: {
            type: String
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
        primaryKey: {
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

    data() {
        return {
            aggregators: [
                { text: 'Match all filters', value: 'AND' },
                { text: 'Match any filter', value: 'OR' }
            ],
            filter: '',
            loadResult: false,
            savedSearchSelected: '',
            selectedColumns: {
                available: [],
                display: []
            },
            tableHeaders: []
        }
    },

    computed: {
        aggregator: {
            get() {
                return this.$store.state.search.savedSearch.content.saved.aggregator
            },
            set(value) {
                this.$store.commit('search/aggregator', value)
            }
        },
        criteria() {
            return this.$store.state.search.savedSearch.content.saved.criteria
        },
        displayColumns: {
            get() {
                return this.$store.state.search.savedSearch.content.saved.display_columns
            },
            set(value) {
                this.$store.commit('search/displayColumns', value)
            }
        },
        filtersGroup() {
            return this.$store.getters['search/filtersGroup']
        },
        filtersFlat() {
            return this.$store.getters['search/filtersFlat']
        },
        filtersList() {
            return this.$store.state.search.filters
        },
        groupBy: {
            get() {
                return this.$store.state.search.savedSearch.content.saved.group_by
            },
            set(value) {
                this.$store.commit('search/groupBy', value)
            }
        },
        name: {
            get() {
                return this.$store.state.search.savedSearch.name
            },
            set(value) {
                this.$store.commit('search/name', value)
            }
        },
        operator() {
            const criteria = this.$store.state.search.savedSearch.content.saved.criteria

            let result = {}
            for (const field in criteria) {
                for (const guid in criteria[field]) {
                    result[guid] = criteria[field][guid].operator
                }
            }

            return result
        },
        savedSearches() {
            return this.$store.state.search.savedSearches
        },
        sortByField() {
            return this.$store.state.search.savedSearch.content.saved.sort_by_field
        },
        sortByOrder() {
            return this.$store.state.search.savedSearch.content.saved.sort_by_order
        }
    },

    created() {
        this.$store.commit('search/filters', JSON.parse(this.filters))
        this.$store.commit('search/displayColumns',  {action: 'add', available: JSON.parse(this.displayFields) })

        if ('' !== this.id) {
            this.$store.commit('search/savedSearchId', this.id)
            this.$store.dispatch('search/savedSearchGet', this.id).then(() => {
                this.$store.dispatch('search/savedSearchesGet')
                this.search()
            })
        }

        if ('' === this.id) {
            this.$store.commit('search/savedSearchModel', this.model)
            this.$store.commit('search/savedSearchUserId', this.userId)
            this.search()
            this.$store.dispatch('search/savedSearchesGet')
        }
    },

    methods: {
        criteriaCreate() {
            if ('' !== this.filter) {
                this.$store.commit('search/criteriaCreate', this.filter)
            }

            this.filter = ''
        },
        criteriaCopy(guid) {
            this.$store.commit('search/criteriaCopy', guid)
        },
        criteriaRemove(guid) {
            this.$store.commit('search/criteriaRemove', guid)
        },
        criteriaUpdated(field, guid, value) {
            this.$store.commit('search/criteriaValue', { field: field, guid: guid, value: value })
        },
        displayColumnsUpdated(action) {
            const payload = Object.assign({}, this.selectedColumns, { action: action })

            this.$store.commit('search/displayColumns', payload)
        },
        displayColumnsSorted(direction) {
            const payload = Object.assign({}, { columns: this.selectedColumns.display }, { direction: direction })

            this.$store.commit('search/displayColumnsSort', payload)
        },
        operatorUpdated(field, guid, value) {
            this.$store.commit('search/criteriaOperator', { field: field, guid: guid, value: value })
        },
        savedSearchCopy() {
            this.$store.dispatch('search/savedSearchCopy', { id: this.savedSearchSelected, user_id: this.userId })
        },
        savedSearchCreate() {
            this.$store.dispatch('search/savedSearchCreate')
        },
        savedSearchDelete() {
            if (this.savedSearchSelected === this.$store.state.search.savedSearch.id) {
                return
            }

            if (! confirm('Are you sure you want to delete this saved search?')) {
                return
            }

            this.$store.dispatch('search/deleteSavedSearch', this.savedSearchSelected).then(() => {
                this.savedSearchSelected = ''
            })
        },
        savedSearchGet() {
            this.$store.dispatch('search/getSavedSearch', this.savedSearchSelected).then(() => {
                this.search()
            })
        },
        saveSearch() {
            this.$store.dispatch('search/saveSearch')
        },
        search() {
            this.loadResult = false

            const self = this
            this.tableHeaders = []
            this.displayColumns.forEach(function (column) {
                self.tableHeaders.push({ value: column, text: self.filtersFlat[column].label })
            })

            // https://github.com/vuejs/Discussion/issues/356#issuecomment-312529480
            this.$nextTick(() => {
                this.loadResult = true
            })
        },
        sortFieldUpdated(value) {
            this.$store.commit('search/sortByField', value)
        },
        sortOrderUpdated(value) {
            this.$store.commit('search/sortByOrder', value)
        }
    }

}
</script>
<style>
.search-form .form-group {
    margin-bottom: 10px;
}
</style>