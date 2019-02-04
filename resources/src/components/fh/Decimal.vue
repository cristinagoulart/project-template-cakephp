<template>
    <div>
        <div class="form-group">
            <input type="number" v-model="val" step="any" max="99999999999" class="form-control input-sm" />
        </div>
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
        },
        value: {
            type: String,
            required: true
        }
    },

    data: function () {
        return {
            val: this.value
        }
    },

    watch: {
        val () {
            this.$emit('value-changed', this.field, this.guid, this.val)
        }
    }

}
</script>