<template>
    <div>
        <div class="form-group">
            <div class="input-group input-group-sm bootstrap-timepicker timepicker">
                <div class="input-group-addon">
                    <i class="fa fa-clock-o"></i>
                </div>
                <input type="text" v-model="val" autocomplete="off" class="form-control" />
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
    },

    mounted: function () {
        const self = this

        $(this.$el).find('input').timepicker({
            showMeridian: false,
            minuteStep: 5,
            defaultTime: false
        }).on('changeTime.timepicker', function (e) {
            self.val = e.time.value
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