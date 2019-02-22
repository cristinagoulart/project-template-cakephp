<template>
    <div>
        <div class="form-group">
            <v-select
                v-model="val"
                placeholder="-- Please choose --"
                :options="options"
                :multiple="multiple"
                :filterable="false"
                @search="onSearch">
                <template slot="no-options">type to search..</template>
                <template slot="option" slot-scope="option">
                    <div class="d-center">{{ option.label }}</div>
                </template>
                <template slot="selected-option" scope="option">
                    <div class="selected d-center">{{ option.label }}</div>
                </template>
            </v-select>
        </div>
    </div>
</template>

<script>
import axios from 'axios'
import lodash from 'lodash'
import vSelect from 'vue-select'

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
        source: {
            type: String,
            required: true
        },
        url: {
            type: String,
            required: true
        },
        value: {
            type: [String, Array],
            default: ''
        }
    },

    data: function () {
        console.log(this.value)
        return {
            options: [],
            val: this.value
        }
    },

    watch: {
        val () {
            console.log(this.val)

            let value = []
            for (const key of Object.keys(this.val)) {
                value.push(this.val[key].value)
            }

            console.log(value)

            this.$emit('input-value-updated', this.field, this.guid, value)
        }
    },

    methods: {
        onSearch(search, loading) {
            loading(true)
            this.options = []
            this.search(search, loading, this)
        },
        search: _.debounce((search, loading, vm, page = 1) => {
            axios({
                method: 'get',
                url: vm.url + '?query=' + encodeURI(search) + '&limit=100&page=' + page,
            }).then(response => {
                const pagination = response.data.pagination

                if ('Users' === vm.source && 1 === pagination.current_page) {
                    vm.options.push({ value: '%%me%%', label: '<< me >>'})
                }

                for (const key of Object.keys(response.data.data)) {
                    vm.options.push({ value: key, label: response.data.data[key]})
                }

                if (pagination.current_page < pagination.page_count) {
                    vm.search(search, loading, vm, pagination.current_page + 1)
                } else {
                    console.log(vm.options)
                    loading(false)
                }
            })
        }, 1000)
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