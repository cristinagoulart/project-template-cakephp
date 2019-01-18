<template>
    <div>
        <label class="control-label" for="save-search">Save Search</label>
        <div class="input-group">
            <div class="form-group input text required">
                <input type="text" v-model="saveSearchName" class="form-control input-sm" placeholder="Saved search name" required="required">
            </div>
            <span class="input-group-btn">
                <button type="button"  @click="saveSearch()" class="btn btn-sm btn-primary">
                    <i class="fa fa-floppy-o" aria-hidden="true"></i>
                </button>
            </span>
        </div>
    </div>
</template>

<script>
import axios from 'axios'

export default {

    data: function () {
        return {
            saveSearchName: ''
        }
    },

    methods: {
        saveSearch: function () {
            if ('' === this.saveSearchName) {
                return
            }

            axios({
                method: 'post',
                url: '/search/saved-searches/add',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(response => {
                let data = response.data.success ? response.data.data : null
                console.log(data)
                if (null === data) {
                    return
                }
            }).catch(error => console.log(error))
        }
    }

}
</script>