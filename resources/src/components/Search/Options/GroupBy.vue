<template>
    <div>
        <label for="group-by">Group By</label>
        <select v-model="groupColumn" class="form-control input-sm">
            <option value="">-- Select group by --</option>
            <option v-for="filter in filters" :value="filter.field" v-if="filter.group === model">
                {{ filter.label }}
            </option>
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
        model: {
            type: String,
            required: true
        }
    },

    data: function () {
        return {
            groupColumn: ''
        }
    }

}
</script>