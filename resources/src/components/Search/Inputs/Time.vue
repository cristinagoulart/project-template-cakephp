<template>
    <div>
        <div class="form-group">
            <div class="input-group input-group-sm bootstrap-timepicker timepicker">
                <div class="input-group-addon">
                    <i class="fa fa-clock-o"></i>
                </div>
                <input type="text" v-model="value" autocomplete="off" class="form-control" />
            </div>
        </div>
    </div>
</template>

<script>
import 'bootstrap-timepicker/css/bootstrap-timepicker.min.css'
import * as $ from 'jquery'
import timepicker from 'bootstrap-timepicker'

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
    },

    mounted: function () {
        const self = this

        $(this.$el).find('input').timepicker({
            showMeridian: false,
            minuteStep: 5,
            defaultTime: false
        }).on('changeTime.timepicker', function(e) {
            self.value = e.time.value
        })
    }

}
</script>
<style>
    .bootstrap-timepicker .input-group-addon i {
        width: auto !important;
        height: auto !important;
    }
</style>