<template>
    <div>
        <div class="form-group">
            <v-select
                v-model="val"
                placeholder="-- Please choose --"
                :options="options.list"
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
        return {
            options: { list: [], full: {} },
            val: this.value
        }
    },

    watch: {
        val () {
            let value = []
            for (const key of Object.keys(this.options.full)) {
                if (-1 < this.val.indexOf(this.options.full[key])) {
                    value.push(key)
                }
            }

            this.$emit('input-value-updated', this.field, this.guid, value)
        }
    },

    methods: {
        onSearch(search, loading, page = 1) {
            loading(true)
            this.options.list = []
            this.search(search, loading, page, this)
        },
        search: _.debounce((search, loading, page, vm) => {
            axios({
                method: 'get',
                url: vm.url + '?query=' + encodeURI(search) + '&page=' + page,
                headers: {
                    'Authorization': 'Bearer ' + vm.$store.state.search.token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(response => {
                const pagination = response.data.pagination

                if ('Users' === vm.source && 1 === pagination.current_page) {
                    vm.options.list.push('<< me >>')
                    vm.options.full['%%me%%'] = '<< me >>'
                }

                if (pagination.current_page < pagination.page_count) {
                    vm.search(search, loading, pagination.current_page + 1, vm)
                }

                for (const key of Object.keys(response.data.data)) {
                    vm.options.list.push(response.data.data[key])
                    vm.options.full[key] = response.data.data[key]
                }

                loading(false)
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