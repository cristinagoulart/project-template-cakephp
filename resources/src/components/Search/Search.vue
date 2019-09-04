<template>
    <div>
        <notifications group="SearchNotification" />
        <div class="box box-solid" v-if="withForm">
            <div class="box-body">
                <form class="search-form" novalidate="novalidate" v-on:submit.prevent="search">
                    <div class="row">
                        <div class="col-lg-3 col-lg-push-9">
                            <div class="row">
                                <div class="col-xs-4">
                                    <div class="form-group">
                                        <select v-model="selectedModuleFilter" class="form-control input-sm">
                                            <option v-for="item in filterModules">{{ item }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xs-8">
                                    <div class="form-group">
                                        <select v-model="filter" class="form-control input-sm" @change="criteriaCreate(filter)">
                                            <option value="">-- Add filter --</option>
                                            <option v-for="item in filtersGroup[selectedModuleFilter]" :value="item.field">{{ item.label }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xs-4">
                                    <div class="form-group">
                                        <select v-model="selectedModuleGroupBy" class="form-control input-sm">
                                            <option v-for="item in displayModules">{{ item }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xs-8">
                                    <div class="form-group">
                                        <select v-model="groupBy" class="form-control input-sm">
                                            <option value="">-- Group by --</option>
                                            <option v-for="item in filtersGroup[selectedModuleGroupBy]" :value="item.field">{{ item.label }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xs-12">
                                    <div class="form-group">
                                        <select v-model="aggregator" class="form-control input-sm">
                                            <option v-for="item in aggregators" :value="item.value">{{ item.text }}</option>
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
                                                    <option v-for="option in operatorTypes[operatorMaps[filtersFlat[field_name].type]]" v-bind:value="option.value">
                                                        {{ option.text }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-xs-6 col-md-5">
                                                <component
                                                  :is="field.type + 'Input'"
                                                  :guid="guid"
                                                  :field="field_name"
                                                  :value="field.value"
                                                  :options="filtersFlat[field_name].options"
                                                  :source="filtersFlat[field_name].source"
                                                  :display-field="filtersFlat[field_name].display_field"
                                                  :multiple="true"
                                                  @input-value-updated="criteriaUpdated"
                                                />
                                            </div>
                                            <div class="col-sm-2 col-md-1">
                                                <button type="button" @click="criteriaRemove(guid)" class="btn btn-default btn-xs"><i class="fa fa-trash" aria-hidden="true"></i></button>
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
                                    <select v-model="selectedColumns.available" class="form-control input-sm" multiple size="8" :disabled="isGroupByEnabled">
                                        <option v-for="filter in filtersGroup[selectedModuleAvailableColumns]" v-if="-1 === displayColumns.indexOf(filter.field)" :value="filter.field">{{ filter.label }}</option>
                                    </select>
                                    <select v-model="selectedModuleAvailableColumns" class="form-control input-sm" :disabled="isGroupByEnabled">
                                        <option v-for="item in displayModules">{{ item }}</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>&nbsp;</label>
                                    <div class="row">
                                        <ColumnsMover
                                          @column-moved="displayColumnsUpdated"
                                          :is-group-by-enabled="isGroupByEnabled"
                                          :selectedColumns="selectedColumns"
                                        />
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <span class="visible-xs visible-sm">&nbsp;</span>
                                    <label for="display-columns">Display Columns</label>
                                    <select v-model="selectedColumns.display" class="form-control input-sm" multiple size="8" :disabled="isGroupByEnabled">
                                        <option v-for="column in displayColumns" :value="filtersFlat[column].field">
                                            {{ filtersFlat[column].label }} <template v-if="filtersFlat[column].group !== modelName">- {{ filtersFlat[column].group }}</template>
                                        </option>
                                    </select>
                                    <div class="row">
                                        <ColumnsSorter
                                          :is-order-disabled="isOrderingDisabled"
                                          @display-columns-sorted="displayColumnsSorted"
                                        />
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
                                                <option v-for="item in savedSearches" :value="item.id">{{ item.name }}</option>
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
                            <button v-if="withExport" type="button" @click="searchExport()" class="btn btn-default btn-sm"><i class="fa fa-download"></i> Export</button>
                            <div v-if="withSets" class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-plus"></i> Add to set <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a href="#" data-toggle="modal" :data-target="'#' + sets.modal">Create new set</a></li>
                                    <li v-if="0 < sets.list.length" role="separator" class="divider"></li>
                                    <li v-for="set in sets.list"><a href="#" @click.prevent="setsAddTo(set.id)">{{ set.name }}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="box box-solid">
            <div class="box-body">
                <table-ajax
                  v-if="loadResult"
                  :url="'/api/' + modelUrl + '/search'"
                  request-type="POST"
                  :data="tableData"
                  :order-field="sortByField"
                  :order-direction="sortByOrder"
                  :model="modelUrl"
                  :primary-key="primaryKey"
                  :headers="tableHeaders"
                  @sort-field-updated="sortFieldUpdated"
                  @sort-order-updated="sortOrderUpdated"
                  :with-batch="withBatch">
                </table-ajax>
            </div>
        </div>
        <div class="modal fade" :id="sets.modal" tabindex="-1" role="dialog" aria-labelledby="mySetsLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Create new set</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group input text required">
                            <label class="control-label" for="sets-name">Name</label>
                            <input type="text" v-model="sets.name" class="form-control" required="required">
                        </div>
                        <div class="form-group input ">
                            <label class="control-label" for="sets-active">Active</label>
                            <div class="clearfix"></div>
                            <input type="checkbox" v-model="sets.active">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="setsCreate()" :disabled="'' === sets.name" class="btn btn-primary">Submit</button>
                        <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import tableAjax from '@/components/ui/TableAjax.vue'
import ColumnsSorter from '@/components/Search/ColumnsSorter.vue'
import ColumnsMover from '@/components/Search/ColumnsMover.vue'
import inputs from '@/components/fh'
import axios from 'axios'
import { mapState, mapGetters, mapActions, mapMutations } from 'vuex'
import { dasherize, underscore } from 'inflected'
import UuidMixin from '@/mixins/uuid.js'
import { FIELD_AGGREGATOR_TYPES, FIELDS_BASIC_TYPES } from '@/utils/search'

export default {
    components: Object.assign({ tableAjax, ColumnsSorter, ColumnsMover }, inputs),
    mixins: [UuidMixin],
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
        },
        withBatch: {
            type: Boolean,
            default: true
        },
        withExport: {
            type: Boolean,
            default: true
        },
        withSets: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            aggregators: FIELD_AGGREGATOR_TYPES,
            basic_types: FIELDS_BASIC_TYPES,
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
            sets: {
                active: true,
                list: [],
                modal: 'sets-modal',
                name: ''
            },
            tableHeaders: [],
            tableData: {},
            // See setter code in this file for info.
            unwatchCriteria: null
        }
    },

    computed: {
        ...mapGetters({
          filtersGroup: 'search/filtersGroup',
          filtersFlat: 'search/filtersFlat'
        }),
        aggregator: {
            get() {
                return this.$store.state.search.savedSearch.content.saved.aggregator
            },
            set(value) {
                this.$store.commit('search/aggregator', value)
            }
        },
        displayColumns: {
            get() {
                return this.$store.state.search.savedSearch.content.saved.display_columns.slice(0)
            },
            set(value) {
                this.$store.commit('search/displayColumns', value)
            }
        },
        groupBy: {
            get() {
                return this.$store.state.search.savedSearch.content.saved.group_by
            },
            set(value) {
                this.$store.commit('search/groupBy', value)
            }
        },
        isGroupByEnabled () {
          return ('' !== this.groupBy) ? true : false
        },
        isOrderingDisabled () {
          let result = false

          if (0 === this.displayColumns.length || 0 === this.selectedColumns.display.length || this.isGroupByEnabled) {
            result = true
          }

          return result
        },
        modelUrl() {
            return dasherize(underscore(this.modelName))
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
            let criteria = this.criteria
            let result = {}
            for (const field in criteria) {
                for (const guid in criteria[field]) {
                    result[guid] = criteria[field][guid].operator
                }
            }

            return result
        },
        filterModules () {
          let result = []

          if (!this.filtersGroup) {
            return result
          }

          Object.keys(this.filtersGroup).forEach((item) => {
            result.push(item)
          })

          return result
        },
        displayModules () {
          let result = []

          if (!this.filtersGroup) {
            return result
          }

          Object.keys(this.filtersGroup).forEach((item) => {
            const association = this.filtersGroup[item].hasOwnProperty(0) ?
              this.filtersGroup[item][0].association :
              ''

            if (-1 === ['oneToMany', 'manyToMany'].indexOf(association)) {
              result.push(item)
            }
          })

          return result
        },
        ...mapState({
          operatorTypes: state => state.search.operators.types,
          operatorMaps: state => state.search.operators.map,
          criteria: state => state.search.savedSearch.content.saved.criteria,
          savedSearch: state => state.search.savedSearch,
          savedSearches: state => state.search.savedSearches,
          sortByField: state => state.search.savedSearch.content.saved.sort_by_field,
          sortByOrder: state => state.search.savedSearch.content.saved.sort_by_order,
          modelName: state => state.search.savedSearch.model,
          filtersList: state => state.search.filters
        })
    },
    created() {
        /**
         * This watcher is responsible for initiating the search execution after all related type
         * filters are done fetching the records IDs and display values using the lookup API endpoint.
         *
         * This logic handles the case where a basic search is executed and related type field(s) are
         * part of the basic search criteria.
         *
         * This watcher is destroyed once the search is initiated for the first time.
         */
        this.unwatchCriteria = this.$watch('criteria', function () {
            const self = this

            const hasOnlyUuids = function (filter) {
                const values = Array.isArray(filter.value) ? filter.value : [filter.value]

                return values.every(item => self.isUuid(item))
            }

            const haveOnlyUuids = function (filters) {
                return Object.values(filters).every(item => {
                    if ('related' !== item.type) {
                        return true
                    }

                    return hasOnlyUuids(item)
                })
            }

            const canSearch = function () {
                return Object.values(self.criteria).every(item => {
                    return haveOnlyUuids(item)
                })
            }

            if (canSearch()) {
                this.search()
            }
        },  { deep: true })

        this.$store.commit('search/filters', JSON.parse(this.filters))

        if ('' !== this.id) {
            this.$store.dispatch('search/savedSearchGet', this.id).then(() => {
                this.savedSearchesGet()
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

            // If there are no criteria, execute search right away, otherwise we
            // need to wait. See unwatchCriteria setter code in this file for info.
            if (0 === Object.keys(this.criteria).length) {
                this.search()
            }

            this.savedSearchesGet()
        }

        this.setsFetch()
    },

    methods: {
        ...mapActions({
          savedSearchSave: 'search/savedSearchSave',
          savedSearchesGet: 'search/savedSearchesGet'
        }),
        ...mapMutations({
          sortFieldUpdated: 'search/sortByField',
          sortOrderUpdated: 'search/sortByOrder'
        }),
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
            let searchId = this.savedSearch.id
            if (this.savedSearchSelected === searchId) {
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
        search() {
            const self = this

            // Remove criteria watcher, see setter code in this file for more info.
            this.unwatchCriteria()

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
                    if (undefined !== self.filtersFlat[column]) {
                      self.tableHeaders.push({ value: column, text: self.filtersFlat[column].label})
                    }
                })
            }

            // https://github.com/vuejs/Discussion/issues/356#issuecomment-312529480
            this.$nextTick(() => {
                this.loadResult = true
            })
        },
        setsCreate() {
            if (! this.withSets) {
                return
            }

            if ('' === this.sets.name) {
                return
            }

            return axios({
                method: 'post',
                url: '/sets/add',
                data: { name: this.sets.name, active: this.sets.active, module: this.modelUrl },
            }).then(response => {
                $('#' + this.sets.modal).modal('hide')
                this.setsFetch()
            }).catch(error => console.log(error))
        },
        setsFetch() {
            if (! this.withSets) {
                return
            }

            return axios({
                method: 'get',
                url: '/sets/index'
            }).then(response => {
                this.sets.list = []
                for (const key of Object.keys(response.data.sets)) {
                    if (! response.data.sets[key].active) {
                        continue
                    }

                    if (response.data.sets[key].module !== this.modelUrl) {
                        continue
                    }

                    this.sets.list.push(response.data.sets[key])
                }
            }).catch(error => console.log(error))
        },
        setsAddTo(setId) {
            if (! this.withSets) {
                return
            }

            this.$store.dispatch('search/savedSearchExport').then(() => {
                const id = this.$store.state.search.exportId

                if ('' === id) {
                    return
                }

                return axios({
                    method: 'put',
                    url: '/sets/assign-search',
                    data: { id: setId, record_id: id },
                }).then(response => {
                    console.log(response)
                }).catch(error => console.log(error))
            })
        },
        searchExport() {
            if (! this.withExport) {
                return
            }

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
        }
    }

}
</script>
<style>
.search-form .form-group {
    margin-bottom: 10px;
}
</style>
