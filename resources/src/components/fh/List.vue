<template>
    <div>
        <div class="form-group">
            <v-select v-model="val" placeholder="-- Please choose --" :options="options" label="label" :multiple="multiple">
                <template slot="option" slot-scope="option">
                    <div v-html="option.label"></div>
                </template>
                <template slot="selected-option" scope="option">
                    <div v-html="option.label"></div>
                </template>
            </v-select>
        </div>
    </div>
</template>

<script>
import vSelect from 'vue-select'
import 'flag-icon-css/css/flag-icon.min.css'

export default {

    components: {
        vSelect
    },

    props: {
        field: {
            type: String,
            required: true
        },
        guid: {
            type: String,
            required: true
        },
        multiple: {
            type: Boolean,
            default: false
        },
        options: {
            type: Array,
            required: true
        },
        value: {
            type: [String, Array],
            default: ''
        }
    },

    data: function () {
        return {
            val: this.multiple ? [] : ''
        }
    },

    created: function () {
        let value = this.value

        if ('' === value || [] === value) {
            return
        }

        if ('string' === typeof value) {
            value = [value]
        }

        const self = this

        const values = this.options.filter(item => -1 < value.indexOf(item.value))

        this.val = this.multiple ? values : values[0]
    },

    watch: {
        val () {
            const selected = this.multiple ? this.val.map(item => item.value) : this.val.value

            this.$emit('input-value-updated', this.field, this.guid, selected)
        }
    }

}
</script>
<style>
.v-select .dropdown-toggle {
     border-radius: 0 !important;
     padding: 0 !important;
}

.v-select .selected-tag {
    font-size: 12px !important;
    margin: 3px 2px !important;
}

.v-select input[type=search] {
    font-size: 12px !important;
    margin: 0 !important;
    padding: 5px 10px !important;
    height: 28px !important;
}
</style>