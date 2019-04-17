<template>
    <div>
        <div class="box box-solid" v-if="withForm">
            <div class="box-body">
                <form class="search-form" novalidate="novalidate" v-on:submit.prevent="search">
                    <div class="row">
                        <div class="col-lg-3 col-lg-push-9">
                            <div class="row">
                                <div class="col-xs-4">
                                    <div class="form-group">
                                        <select v-model="selectedModuleFilter" class="form-control input-sm">
                                            <option v-for="(group_filters, group) in filtersGroup">{{ group }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xs-8">
                                    <div class="form-group">
                                        <select v-model="filter" class="form-control input-sm" v-on:change="criteriaCreate(filter)">
                                            <option value="">-- Add filter --</option>
                                            <option v-for="filter in filtersGroup[selectedModuleFilter]" :value="filter.field">{{ filter.label }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xs-4">
                                    <div class="form-group">
                                        <select v-model="selectedModuleGroupBy" class="form-control input-sm">
                                            <option v-for="(group_filters, group) in filtersGroup">{{ group }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xs-8">
                                    <div class="form-group">
                                        <select v-model="groupBy" class="form-control input-sm">
                                            <option value="">-- Group by --</option>
                                            <option v-for="filter in filtersGroup[selectedModuleGroupBy]" :value="filter.field">{{ filter.label }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xs-12">
                                    <div class="form-group">
                                        <select v-model="aggregator" class="form-control input-sm">
                                            <option v-for="aggregator in aggregators" :value="aggregator.value">{{ aggregator.text }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-9 col-lg-pull-3">
                            <fieldset>
                                <template v-for="(fields, field_name) in criteria">
                                    <div v-for="(field, guid) in fields" class="form-group">
                                        <div class="row">
                                            <div class="col-xs-12 col-md-4 col-lg-3">
                                                <label>{{ filtersFlat[field_name].label }}
                                                    <template v-if="filtersFlat[field_name].group !== modelName"><i class="fa fa-info-circle" :title="filtersFlat[field_name].group"></i></template>
                                                </label>
                                            </div>
                                            <div class="col-xs-4 col-md-2 col-lg-2">
                                                <select v-model="operator[guid]" class="form-control input-sm" v-on:change="operatorUpdated(field_name, guid, operator[guid])">
                                                    <option v-for="option in $store.state.search.operators.types[$store.state.search.operators.map[filtersFlat[field_name].type]]" v-bind:value="option.value">
                                                        {{ option.text }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-xs-6 col-md-5">
                                                <component :is="field.type + 'Input'" :guid="guid" :field="field_name" :key="guid + field.value" :value="field.value" :options="filtersFlat[field_name].options" :source="filtersFlat[field_name].source" :display-field="filtersFlat[field_name].display_field" :multiple="true" @input-value-updated="criteriaUpdated" />
                                            </div>
                                            <div class="col-sm-2 col-md-1">
                                                <button type="button" @click="criteriaRemove(guid)" class="btn btn-default btn-xs"><i class="fa fa-trash" aria-hidden="true"></i></button>
                                                <!-- <div class="input-sm">
                                                    <button type="button" @click="criteriaCopy(guid)" class="btn btn-default btn-xs"><i class="fa fa-clone" aria-hidden="true"></i></button>
                                                </div> -->
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </fieldset>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-lg-9">
                            <hr class="visible-xs visible-sm visible-md" />
                            <div class="row">
                                <div class="col-md-5">
                                    <label for="available-columns">Available Columns</label>
                                    <select v-model="selectedColumns.available" class="form-control input-sm" multiple size="8" :disabled="'' !== groupBy">
                                        <option v-for="filter in filtersGroup[selectedModuleAvailableColumns]" v-if="-1 === displayColumns.indexOf(filter.field)" :value="filter.field">{{ filter.label }}</option>
                                    </select>
                                    <select v-model="selectedModuleAvailableColumns" class="form-control input-sm" :disabled="'' !== groupBy">
                                        <option v-for="(group_filters, group) in filtersGroup">{{ group }}</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>&nbsp;</label>
                                    <div class="row">
                                        <div class="col-xs-6 col-md-12">
                                            <button type="button" @click="displayColumnsUpdated('add')" class="btn btn-block btn-sm" :disabled="'' !== groupBy || 0 === selectedColumns.available.length">
                                                <span class="visible-md visible-lg"><i class="fa fa-angle-right"></i></span>
                                                <span class="visible-xs visible-sm"><i class="fa fa-angle-down"></i></span>
                                            </button>
                                        </div>
                                        <span class="visible-md visible-lg">&nbsp;</span>
                                        <div class="col-xs-6 col-md-12">
                                            <button type="button" @click="displayColumnsUpdated('remove')" class="btn btn-block btn-sm" :disabled="'' !== groupBy || 0 === selectedColumns.display.length">
                                                <span class="visible-md visible-lg"><i class="fa fa-angle-left"></i></span>
                                                <span class="visible-xs visible-sm"><i class="fa fa-angle-up"></i></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <span class="visible-xs visible-sm">&nbsp;</span>
                                    <label for="display-columns">Display Columns</label>
                                    <select v-model="selectedColumns.display" class="form-control input-sm" multiple size="8" :disabled="'' !== groupBy">
                                        <option v-for="column in displayColumns" :value="filtersFlat[column].field">
                                            {{ filtersFlat[column].label }} <template v-if="filtersFlat[column].group !== modelName">- {{ filtersFlat[column].group }}</template>
                                        </option>
                                    </select>
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <button type="button" @click="displayColumnsSorted('up')" :disabled="0 === displayColumns.length || 0 === selectedColumns.display.length || '' !== groupBy" class="btn btn-block btn-sm">
                                                <i class="fa fa-angle-up"></i>
                                            </button>
                                        </div>
                                        <div class="col-xs-6">
                                            <button type="button" @click="displayColumnsSorted('down')" :disabled="0 === displayColumns.length || 0 === selectedColumns.display.length || '' !== groupBy" class="btn btn-block btn-sm">
                                                <i class="fa fa-angle-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-3">
                            <hr class="visible-xs visible-sm visible-md" />
                            <div class="row">
                                <div class="col-md-6 col-lg-12">
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
                                </div>
                                <div class="col-md-6 col-lg-12">
                                    <div class="form-group">
                                        <label class="control-label" for="save-search">Save Search</label>
                                        <div class="input-group">
                                            <div class="form-group input text required">
                                                <input type="text" v-model="name" class="form-control input-sm" placeholder="Saved search name" required="required">
                                            </div>
                                            <span class="input-group-btn">
                                                <button type="button" @click="savedSearchSave()" :disabled="'' === name" class="btn btn-sm btn-primary"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12">
                            <hr class="visible-xs visible-sm visible-md" />
                            <span class="visible-lg">&nbsp;</span>
                            <button class="btn btn-primary btn-sm" type="submit"><i class="fa fa-search"></i> Search</button>
                            <button type="button" @click="searchReset()" class="btn btn-default btn-sm"><i class="fa fa-undo"></i> Reset</button>
                            <button type="button" @click="searchExport()" class="btn btn-default btn-sm"><i class="fa fa-download"></i> Export</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="box box-solid">
            <div class="box-body">
                <table-ajax v-if="loadResult" :url="'/api/' + modelUrl + '/search'" request-type="POST" :data="tableData" :order-field="sortByField" :order-direction="sortByOrder" :model="modelUrl" :primary-key="primaryKey" :headers="tableHeaders" @sort-field-updated="sortFieldUpdated" @sort-order-updated="sortOrderUpdated"></table-ajax>
            </div>
        </div>
    </div>
</template>

<script>
import tableAjax from '@/components/ui/TableAjax.vue'
import inputs from '@/components/fh'
import axios from 'axios'

export default {

    components: Object.assign({ tableAjax }, inputs),

    props: {
        displayFields: {
            type: Array,
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
        primaryKey: {
            type: String,
            required: true
        },
        searchQuery: {
            type: String,
            default: ''
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
            basic_types: ['string', 'text', 'textarea', 'related', 'email', 'url', 'phone', 'integer'],
            filter: '',
            selectedModuleAvailableColumns: this.model,
            selectedModuleFilter: this.model,
            selectedModuleGroupBy: this.model,
            loadResult: false,
            savedSearchSelected: '',
            selectedColumns: {
                available: [],
                display: []
            },
            tableHeaders: [],
            tableData: {}
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
                return this.$store.state.search.savedSearch.content.saved.display_columns.slice(0)
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
        modelName() {
            return this.$store.state.search.savedSearch.model
        },
        modelUrl() {
            /**
             * @link https://coderwall.com/p/hpq7sq/undescorize-dasherize-capitalize-string-prototype
             */
            const dasherize = function (string) {
                return string.replace(/[A-Z]/g, function(char, index) {
                    return (index !== 0 ? '-' : '') + char.toLowerCase()
                })
            }

            return dasherize(this.modelName)
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

        if ('' !== this.id) {
            this.$store.dispatch('search/savedSearchGet', this.id).then(() => {
                this.$store.dispatch('search/savedSearchesGet')
                this.search()
            })
        }

        if ('' === this.id) {
            if ('' !== this.searchQuery) {
                this.basicSearch(this.searchQuery)
            }
            this.$store.commit('search/displayColumns',  {action: 'add', available: this.displayFields })
            this.$store.commit('search/savedSearchModel', this.model)
            this.$store.commit('search/savedSearchUserId', this.userId)
            this.search()
            this.$store.dispatch('search/savedSearchesGet')
        }
    },

    methods: {
        basicSearch(query) {
            const self = this

            this.displayFields.map(function(field) {
                const filter = self.$store.state.search.filters.filter(filter => filter.field === field)
                if (-1 !== self.basic_types.indexOf(filter[0].type)) {
                    // switch to OR aggregator on basic search
                    self.aggregator = 'OR'
                    self.criteriaCreate(filter[0].field, query)
                }
            })
        },
        criteriaCreate(filter, value = '') {
            if ('' !== filter) {
                this.$store.commit('search/criteriaCreate', { field: filter, value: value })
            }

            this.filter = ''
        },
        criteriaCopy(guid) {
            // skipping for now, this functionality becomes tricky when you consider events, vuex store etc.
            return

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
        savedSearchDelete() {
            if (this.savedSearchSelected === this.$store.state.search.savedSearch.id) {
                return
            }

            if (! confirm('Are you sure you want to delete this saved search?')) {
                return
            }

            this.$store.dispatch('search/savedSearchDelete', this.savedSearchSelected).then(() => {
                this.savedSearchSelected = ''
            })
        },
        savedSearchGet() {
            this.$store.dispatch('search/savedSearchGet', this.savedSearchSelected).then(() => {
                this.search()
            })
        },
        savedSearchSave() {
            this.$store.dispatch('search/savedSearchSave')
        },
        search() {
            const self = this

            this.loadResult = false

            this.tableHeaders = []
            this.tableData = { criteria: this.criteria, group_by: this.groupBy, aggregator: this.aggregator }

            if (this.groupBy) {
                this.selectedModuleGroupBy = self.filtersFlat[this.groupBy].group
                this.tableHeaders.push({ value: this.groupBy, text: self.filtersFlat[this.groupBy].label })
                this.tableHeaders.push({ value: this.modelName + '.total', text: 'Total' })
            }


            if (! this.groupBy) {
                this.displayColumns.forEach(function (column) {
                    self.tableHeaders.push({ value: column, text: self.filtersFlat[column].label })
                })
            }

            // https://github.com/vuejs/Discussion/issues/356#issuecomment-312529480
            this.$nextTick(() => {
                this.loadResult = true
            })
        },
        searchExport() {
            this.$store.dispatch('search/savedSearchExport').then(() => {
                const id = this.$store.state.search.exportId

                if ('' === id) {
                    return
                }

                let name = this.$store.state.search.savedSearch.name
                name = '' === name ? this.modelName : name

                const date = new Date()

                const addZero = function (value) {
                    return value < 10 ? '0' + value : value
                }

                const datetime = [
                    date.getFullYear(),
                    addZero(date.getMonth() + 1),
                    addZero(date.getDate()),
                    addZero(date.getHours()),
                    addZero(date.getMinutes()),
                    addZero(date.getSeconds())
                ]

                window.location.href = encodeURI('/' + this.modelUrl + '/export-search/' + id + '/' + name + ' ' + datetime.join(''))
            })
        },
        searchReset() {
            this.$store.commit('search/aggregator', 'AND')
            this.$store.commit('search/groupBy', '')
            this.$store.commit('search/name', '')
            this.$store.commit('search/savedSearchId', '')
            this.$store.commit('search/sortByField', '')
            this.$store.commit('search/sortByOrder', 'asc')

            Object.keys(this.criteria).map(
                (key) => Object.keys(this.criteria[key]).map(
                    (guid) => this.$store.commit('search/criteriaRemove', guid)
                )
            )

            this.$store.commit('search/displayColumns', { action: 'remove', display: this.displayColumns })
            this.$store.commit('search/displayColumns', { action: 'add', available: this.displayFields })
            this.selectedColumns.available = []
            this.selectedColumns.display = []
            this.selectedModuleAvailableColumns = this.model
            this.selectedModuleFilter = this.model
            this.selectedModuleGroupBy = this.model

            this.search()
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