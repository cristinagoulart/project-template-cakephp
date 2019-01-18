<template>
    <div>
        <div class="form-group">
            <select v-model="value" class="form-control">
                <option v-for="(text, val) in options" v-bind:value="val">
                    {{ text }}
                </option>
            </select>
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
            value: this.field.value,
            options: this.field.options
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