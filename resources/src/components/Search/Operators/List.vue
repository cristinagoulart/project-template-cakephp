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
            type: Object,
            required: true
        }
    },

    data: function () {
        return {
            operator: '',
            options: [
                { value: 'is', text: 'is' },
                { value: 'is_not', text: 'is not' },
                { value: 'in', text: 'in' }
            ]
        }
    },

    watch: {
        operator () {
            this.field.operator = this.operator

            this.$emit('operator-changed', this.field)
        }
    },

    mounted() {
        this.operator = this.field.operator || this.options[0].value
    }

}
</script>