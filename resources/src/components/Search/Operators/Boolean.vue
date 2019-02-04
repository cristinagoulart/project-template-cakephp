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

    data () {
        return {
            options: [
                { value: 'is', text: 'is' },
                { value: 'is_not', text: 'is not' }
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