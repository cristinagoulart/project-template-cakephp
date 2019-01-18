<template>
    <div class="box box-primary">
        <div class="box-body">
            <form id="SearchFilterForm" class="search-form" novalidate="novalidate" v-on:submit.prevent="search">
                <div class="row">
                    <div class="col-lg-3 col-lg-push-9">
                        <div class="form-group">
                            <search-filters :filters="filters"></search-filters>
                        </div>
                        <div class="form-group">
                            <search-aggregators v-if="isRenderable" :aggregator="aggregator"></search-aggregators>
                        </div>
                    </div>
                    <hr class="visible-xs visible-sm visible-md" />
                    <div class="col-lg-9 col-lg-pull-3">
                        <search-criteria :filters="filters" :criteria="criteria" v-if="isRenderable" @criteria-updated="criteriaUpdated"></search-criteria>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8 col-lg-9">
                        <div class="row">
                            <search-display-columns v-if="isRenderable" :model="model" :available-columns="availableColumns" :display-columns="displayColumns"></search-display-columns>
                            <div class="col-lg-2">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12">
                                        <div class="form-group">
                                            <search-sort-field v-if="isRenderable" :filters="filters" :field="sortByField"></search-sort-field>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-12">
                                        <div class="form-group">
                                            <search-sort-order v-if="isRenderable" :sort-dir="sortByOrder"></search-sort-order>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-12">
                                        <div class="form-group">
                                            <search-group-by :filters="filters" :model="model"></search-group-by>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-sm" type="submit"><i class="fa fa-search"></i> Search</button>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <div class="form-group">
                            <search-form-saved-searches :model="model" :user="user"></search-form-saved-searches>
                        </div>
                        <div class="form-group">
                            <search-form-save-search></search-form-save-search>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import axios from 'axios'
import components from '@/components/Search/Options'
import SearchMixin from '@/mixins/searchMixin'

export default {

    mixins: [
        SearchMixin
    ],

    components: components,

    props: {
        searchId: {
            type: String,
            required: true
        },
        filters: {
            type: Array,
            required: true
        },
        model: {
            type: String,
            required: true
        },
        token: {
            type: String,
            required: true
        },
        user: {
            type: Object,
            required: true
        }
    },

    data: function () {
        return {
            aggregator: '',
            availableColumns: [],
            criteria: [],
            displayColumns: [],
            isRenderable: false,
            sortByField: '',
            sortByOrder: ''
        }
    },

    created: function () {
        axios({
            method: 'get',
            url: '/search/saved-searches/view/' + this.searchId,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(response => {
            let data = response.data.success ? response.data.data : null
            if (null === data) {
                return
            }

            console.log(data.content.saved)

            this.aggregator = data.content.saved.aggregator || 'AND'
            this.availableColumns = this.filters.filter(filter => -1 === data.content.saved.display_columns.indexOf(filter.field))
            this.criteria = this.normalizeCriteria(data.content.saved.criteria)
            this.displayColumns = this.filters.filter(filter => -1 < data.content.saved.display_columns.indexOf(filter.field))
            this.sortByField = data.content.saved.sort_by_field
            this.sortByOrder = data.content.saved.sort_by_order || 'asc'
            this.isRenderable = true
        }).catch(error => console.log(error))
    },

    methods: {
        search: function () {
            if (! this.validate()) {
                return
            }

            const model = this.model.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase()

            axios({
                method: 'post',
                url: '/api/' + model + '/search',
                data: {
                    content: this.content,
                    related_model: this.relatedModel,
                    related_id: this.relatedId
                },
                headers: {
                    'Authorization': 'Bearer ' + this.token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.data.success) {
                    console.log(response)
                }
            })
            .catch(error => console.log(error))
        },

        normalizeCriteria: function (data) {
            let result = []
            for (const field in data) {
                for (const key in data[field]) {
                    const filter = this.filters.filter(filter => {
                        return filter.field === field
                    })

                    result.push(Object.assign(data[field][key], { field: field, label: filter[0].label, guid: this.guid() }))
                }
            }

            return result
        },

        criteriaUpdated: function (data) {
            this.criteria = data
        },

        timestamp: function () {
            return Math.round(1000000 * Math.random())
        },

        validate: function () {
            return true
        }

    }

}
</script>