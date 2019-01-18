<template>
    <div>
        <div class="form-group">
            <input type="number" v-model="value" step="1" max="99999999999" class="form-control input-sm" />
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
            value: this.field.value
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