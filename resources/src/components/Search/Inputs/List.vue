<template>
    <div>
        <div class="form-group">
            <v-select v-model="value" placeholder="-- Please choose --" :options="options" :multiple="true">
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
            type: Object,
            required: true
        }
    },

    data: function () {
        let data = {
            value: this.field.value,
            options: []
        }

        for (const key of Object.keys(this.field.options)) {
            data.options.push(this.field.options[key])
        }

        return data
    },

    watch: {
        value () {
            this.field.value = []

            for (const key of Object.keys(this.field.options)) {
                if (-1 < this.value.indexOf(this.field.options[key])) {
                    this.field.value.push(key)
                }
            }

            this.$emit('value-changed', this.field)
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