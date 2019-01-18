<template>
    <div>
        <label for="sort-field">Sort Field</label>
        <select v-model="sortField" class="form-control input-sm">
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
        },
        field: {
            type: String,
            required: true
        }
    },

    data: function () {
        return {
            sortField: this.field
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
    }

}
</script>