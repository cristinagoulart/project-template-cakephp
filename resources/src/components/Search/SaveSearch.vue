<template>
  <div class="form-group">
    <label class="control-label" for="save-search">Save Search</label>
    <div class="input-group">
      <div class="form-group input text required">
        <input type="text" v-model="name" class="form-control input-sm" placeholder="Saved search name" required="required">
      </div>
      <span class="input-group-btn">
        <button type="button" @click="save()" :disabled="!name" class="btn btn-sm btn-primary"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
      </span>
    </div>
  </div>
</template>
<script>
export default {
  name: 'SaveSearch',
  computed: {
    name: {
      get() {
        return this.$store.state.search.name
      },
      set(value) {
        this.$store.commit('search/name', value)
      }
    }
  },
  methods: {
    save() {
      this.$store.dispatch('search/savedSearchSave', { id: this.selected, user_id: this.userId })
    }
  }
}
</script>
