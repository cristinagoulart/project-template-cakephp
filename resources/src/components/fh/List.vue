<template>
    <div>
        <div class="form-group">
            <v-select v-model="val" placeholder="-- Please choose --" :options="labels" :multiple="true">
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
        options: {
            type: Object,
            required: true
        },
        value: {
            type: [String, Array],
            default: ''
        }
    },

    data: function () {
        let result = {
            val: 'string' === typeof this.value ? (this.value ? [this.value] : []) : this.value,
            labels: Object.values(this.options)
        }

        return result
    },

    watch: {
        val () {
            let value = []
            for (const key of Object.keys(this.options)) {
                if (-1 < this.val.indexOf(this.options[key])) {
                    value.push(key)
                }
            }

            this.$emit('value-changed', this.field, this.guid, value)
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