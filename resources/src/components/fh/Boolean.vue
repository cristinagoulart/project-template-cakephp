<template>
    <div>
        <div class="form-group">
            <input type="checkbox" v-model="val" class="square">
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
            type: String,
            required: true
        },
        guid: {
            type: String,
            required: true
        },
        value: {
            type: [String, Number],
            required: true
        }
    },

    data: function () {
        return {
            val: +this.value
        }
    },

    watch: {
        val () {
            this.$emit('value-changed', this.field, this.guid, this.val)
        }
    },

    mounted () {
        const self = this
        const $input = $(this.$el).find('input')

        $input.iCheck({
            checkboxClass: 'icheckbox_square',
            radioClass: 'iradio_square'
        })

        $input.on('ifChecked', function (e) {
            self.val = 1
        })

        $input.on('ifUnchecked', function (e) {
            self.val = 0
        })
    }

}
</script>