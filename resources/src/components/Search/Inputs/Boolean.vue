<template>
    <div>
        <div class="form-group">
            <input type="checkbox" v-model="value" class="square">
        </div>
    </div>
</template>

<script>
import 'icheck/skins/square/square.css'
import * as $ from 'jquery'
import icheck from 'icheck'

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
    },

    mounted: function () {
        const self = this
        const $input = $(this.$el).find('input')

        $input.iCheck({
            checkboxClass: 'icheckbox_square',
            radioClass: 'iradio_square'
        })

        $input.on('ifChecked', function (e) {
            self.value = true
        })

        $input.on('ifUnchecked', function (e) {
            self.value = false
        })
    }

}
</script>