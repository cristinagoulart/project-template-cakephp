<template>
    <div>
        <div class="form-group">
            <div class="input-group input-group-sm">
                <div class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                </div>
                <input type="text" autocomplete="off" class="form-control" />
            </div>
        </div>
    </div>
</template>

<script>
import * as $ from 'jquery'
import daterangepicker from 'daterangepicker'
import moment from 'moment'

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
        const $input = $(this.$el).find('input')
        const options = {
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear',
                firstDay: 1,
                format: 'YYYY-MM-DD HH:mm'
            },
            maxYear: 2050,
            minYear: 1900,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Tomorrow': [moment().add(1, 'days'), moment().add(1, 'days')]
            },
            showDropdowns: true,
            singleDatePicker: true,
            timePicker: true,
            timePicker24Hour: true,
            timePickerIncrement: 5
        }

        $input.daterangepicker(options)

        Object.keys(options.ranges).forEach(function (item) {
            // convert magic value to label, for example "%%today%%" becomes "Today"
            if (self.value === '%%' + item.toLowerCase() + '%%') {
                $input.val(item)
            }
        })

        $input.on('apply.daterangepicker', function (e, picker) {
            $(this).val('Custom Range' === picker.chosenLabel ?
                picker.startDate.format(picker.locale.format) :
                picker.chosenLabel
            )
            self.field.value = 'Custom Range' === picker.chosenLabel ?
                picker.startDate.format(picker.locale.format) :
                '%%' + picker.chosenLabel.toLowerCase() + '%%'
        })

        $input.on('cancel.daterangepicker', function (e, picker) {
            $(this).val('')
            self.field.value = ''
        })
    }

}
</script>
<style src='daterangepicker/daterangepicker.css'></style>