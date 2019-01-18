<template>
    <div>
        <search-form
            :search-id="searchId"
            :filters="searchFilters"
            :model="model"
            :token="token"
            :user="searchUser"
        ></search-form>
        <search-result></search-result>
    </div>
</template>

<script>
import SearchForm from '@/components/Search/Form.vue'
import SearchResult from '@/components/Search/Result.vue'
import axios from 'axios'

export default {

    components: {
        SearchForm,
        SearchResult
    },

    props: {
        searchId: {
            type: String,
            required: true
        },
        filters: {
            type: String,
            required: true
        },
        model: {
            type: String,
            required: true
        },
        token: {
            type: String,
            required: true
        },
        user: {
            type: String,
            required: true
        }
    },

    computed: {
        searchFilters: function () {
            let self = this
            let result = JSON.parse(this.filters)
            result.forEach(function (filter) {
                Object.assign(filter, { value: '', operator: '' })
            })

            return result
        },
        searchUser: function () {
            return JSON.parse(this.user)
        }
    }

}
</script>