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
                { value: 'contains', text: 'contains' },
                { value: 'not_contains', text: 'does not contain' },
                { value: 'starts_with', text: 'starts with' },
                { value: 'ends_with', text: 'ends with' }
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