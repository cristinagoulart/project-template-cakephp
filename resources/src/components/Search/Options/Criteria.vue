<template>
    <div>
        <fieldset>
            <div v-for="(field, index) in criteria" v-bind:key="index" class="form-group search-field-wrapper">
                <div class="row">
                    <div class="col-xs-12 col-md-3 col-lg-2"><label>{{ field.label }}</label></div>
                    <div class="col-xs-4 col-md-2 col-lg-3">
                        <component :is="field.type + 'Operator'" :field="field" @operator-changed="operatorChanged" />
                    </div>
                    <div class="col-xs-6 col-md-5 col-lg-4">
                        <component :is="field.type + 'Input'" :field="field" @value-changed="valueChanged" />
                    </div>
                    <div class="col-xs-2">
                        <div class="input-sm">
                            <button type="button" @click="removeCriteria(index)" class="btn btn-default btn-xs">
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </button>
                            <button type="button" @click="copyCriteria(index)" class="btn btn-default btn-xs">
                                <i class="fa fa-clone" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>
</template>

<script>
import axios from 'axios'
import inputs from '@/components/Search/Inputs'
import operators from '@/components/Search/Operators'
import SearchMixin from '@/mixins/searchMixin'

let compoments = Object.assign({}, inputs, operators)

export default {

    mixins: [
        SearchMixin
    ],

    components: compoments,

    props: {
        criteria: {
            type: Array,
            required: true
        },
        filters: {
            type: Array,
            required: true
        }
    },

    watch: {
        criteria: {
            handler: function () {
                this.$emit('criteria-updated', this.criteria)
            },
            deep: true
        }
    },

    mounted: function () {
        this.$root.$on('filter-selected', (filter) => {
            this.createCriteria(filter)
        })
    },

    methods: {
        createCriteria (selected) {
            const filter = this.filters.filter(filter => {
                return filter.field === selected
            })

            let result = Object.assign({}, filter[0], { guid: this.guid() })

            this.criteria.push(result)
        },

        copyCriteria (index) {
            let result = Object.assign({}, this.criteria[index])
            result.guid = this.guid()

            this.criteria.splice(index, 0, result)
        },

        removeCriteria (index) {
            this.criteria.splice(index, 1)
        },

        operatorChanged (field) {
            this.criteria.forEach((element, index) => {
                if (element.guid === field.guid) {
                    element.operator = field.operator
                }
            })
        },

        valueChanged (field) {
            this.criteria.forEach((element, index) => {
                if (element.guid === field.guid) {
                    element.value = field.value
                }
            })
        }
    }

}
</script>