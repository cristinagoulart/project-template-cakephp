<template>
    <div>
        <div class="form-group">
            <input type="checkbox" v-model="value" class="square">
        </div>
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
            value: !!+this.field.value
        }
    },

    watch: {
        value () {
            this.field.value = this.value

            this.$emit('value-changed', this.field)
        }
    }

}
</script>