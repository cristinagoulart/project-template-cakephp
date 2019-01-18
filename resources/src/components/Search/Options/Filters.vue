<template>
    <div>
        <select v-model="filter" class="form-control input-sm" id="addFilter" v-on:change="filterSelected()">
            <option value="">-- Add filter --</option>
            <template v-for="(group_filters, group) in filtersList">
                <optgroup :label="group">
                    <option v-for="(name, value) in group_filters" :value="value">
                        {{ name }}
                    </option>
                </optgroup>
            </template>
        </select>
    </div>
</template>

<script>
import axios from 'axios'

export default {

    props: {
        filters: {
            type: Array,
            required: true
        }
    },

    data: function () {
        return {
            filter: ''
        }
    },

    computed: {
        filtersList: function () {
            let result = {}
            for (var index in this.filters) {
                let filter = this.filters[index]
                if (! result.hasOwnProperty(filter.group)) {
                    result[filter.group] = {}
                }
                result[filter.group][filter.field] = filter.label
            }

            return result
        }
    },

    methods: {
        filterSelected: function () {
            if ('' !== this.filter) {
                this.$root.$emit('filter-selected', this.filter)
            }

            this.filter = ''
        }
    }

}
</script>