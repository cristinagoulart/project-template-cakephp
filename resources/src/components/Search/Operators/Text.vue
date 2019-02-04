<template>
    <div>
        <select v-model="operator" class="form-control input-sm">
            <option v-for="option in options" v-bind:value="option.value">
                {{ option.text }}
            </option>
        </select>
    </div>
</template>

<script>

export default {

    props: {
        field: {
            type: String,
            required: true
        },
        guid: {
            type: String,
            required: true
        }
    },

    data: function () {
        return {
            options: [
                { value: 'contains', text: 'contains' },
                { value: 'not_contains', text: 'does not contain' },
                { value: 'starts_with', text: 'starts with' },
                { value: 'ends_with', text: 'ends with' }
            ]
        }
    },

    computed: {
        operator: {
            get () {
                return this.$store.state.search.savedSearch.content.saved.criteria[this.field][this.guid].operator
            },
            set (value) {
                this.$store.commit('search/criteriaOperator', {
                    field: this.field,
                    guid: this.guid,
                    value: value
                })
            }
        }
    }

}
</script>