<template>
    <div>
        <select v-model="aggr" class="form-control input-sm">
            <option v-for="aggregator in aggregators" :value="aggregator.value">
                {{ aggregator.text }}
            </option>
        </select>
    </div>
</template>

<script>
import axios from 'axios'

export default {

    props: {
        aggregator: {
            type: String,
            required: true
        }
    },

    data: function () {
        return {
            aggr: this.aggregator,
            aggregators: [
                { text: 'Match all filters', value: 'AND' },
                { text: 'Match any filter', value: 'OR' }
            ],
        }
    }

}
</script>