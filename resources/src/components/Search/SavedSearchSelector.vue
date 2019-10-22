<template>
  <div class="form-group">
    <label class="control-label" for="saved-searches">Saved Searches</label>
    <div class="input-group">
      <select v-model="selected" class="form-control input-sm">
        <option value="">-- Please choose --</option>
        <option v-for="item in savedSearches" :value="item.id">{{ item.name }}</option>
      </select>
      <span class="input-group-btn">
        <button type="button" @click="get()" :disabled="!selected" class="btn btn-default btn-sm">
          <i class="fa fa-eye"></i>
        </button>
        <button type="button" @click="copy()" :disabled="!selected" class="btn btn-default btn-sm">
          <i class="fa fa-clone"></i>
        </button>
        <button type="button" @click="remove()" :disabled="!selected || selected === searchId" class="btn btn-danger btn-sm">
          <i class="fa fa-trash"></i>
        </button>
      </span>
    </div>
  </div>
</template>
<script>
import { mapState } from 'vuex'

export default {
  name: 'SavedSearchSelector',
  computed: {
    ...mapState({
      savedSearches: state => state.search.savedSearches,
      searchId: state => state.search.id,
      userId: state => state.search.user_id
    })
  },
  data() {
    return {
      selected: ''
    }
  },
  created() {
    this.$store.dispatch('search/savedSearchesGet')
  },
  methods: {
    copy() {
      this.$store.dispatch('search/savedSearchCopy', { id: this.selected, user_id: this.userId })
    },
    get() {
      this.$store.dispatch('search/savedSearchGet', this.selected).then(() => {
        this.$emit('saved-search-fetched')
      })
    },
    remove() {
      if (this.selected === this.searchId) {
        return
      }

      if (! confirm('Are you sure you want to delete this saved search?')) {
        return
      }

      this.$store.dispatch('search/savedSearchDelete', this.selected).then(() => {
        this.selected = ''
      })
    }
  }
}
</script>
