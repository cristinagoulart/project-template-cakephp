<template>
    <div v-if="savedSearches">
        <label class="control-label" for="saved-searches">Saved Searches</label>
        <div class="input-group">
            <select v-model="savedSearch" class="form-control input-sm">
                <option v-for="savedSearch in savedSearches" :value="savedSearch.id">
                    {{ savedSearch.name }}
                </option>
            </select>
            <span class="input-group-btn">
                <button type="button" id="savedCriteriasView" class="btn btn-default btn-sm">
                    <i class="fa fa-eye"></i>
                </button>
                <button type="button" id="savedCriteriasCopy" class="btn btn-default btn-sm">
                    <i class="fa fa-clone"></i>
                </button>
                <button type="button" id="savedCriteriasDelete" class="btn btn-danger btn-sm">
                    <i class="fa fa-trash"></i>
                </button>
            </span>
        </div>
    </div>
</template>

<script>
import axios from 'axios'

export default {

    props: {
        model: {
            type: String,
            required: true
        },
        user: {
            type: Object,
            required: true
        }
    },

    data: function () {
        return {
            savedSearches: null,
            savedSearch: ''
        }
    },

    mounted: function () {
        axios({
            method: 'get',
            url: '/search/saved-searches/index',
            params: {
                model: this.model,
                system: 0,
                user_id: this.user.id
            },
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(response => {
            this.savedSearches = response.data.success ? response.data.data : null
            if (null !== this.savedSearches) {
                this.savedSearch = this.savedSearches[0].id
            }
        }).catch(error => console.log(error))
    }

}
</script>