<template>
    <div>
        <div class="form-group">
            <input type="email" v-model="val" class="form-control input-sm" />
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
            default: ''
        }
    },

    data: function () {
        return {
            val: this.value
        }
    },

    watch: {
        val () {
            this.$emit('input-value-updated', this.field, this.guid, this.val)
        }
    }

}
</script>