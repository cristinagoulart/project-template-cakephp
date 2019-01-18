<template>
    <div>
        <label for="sort-order">Sort Order</label>
        <select v-model="sortDirection" class="form-control input-sm">
            <option v-for="sortDirection in sortDirections" :value="sortDirection.value">
                {{ sortDirection.text }}
            </option>
        </select>
    </div>
</template>

<script>
import axios from 'axios'

export default {

    props: {
        sortDir: {
            type: String,
            required: true
        }
    },

    data: function () {
        return {
            sortDirection: this.sortDir,
            sortDirections: [
                { text: 'Ascending', value: 'asc' },
                { text: 'Descending', value: 'desc' }
            ]
        }
    }

}
</script>