<template>
  <div>
    <div class="col-xs-6 col-md-12">
        <button type="button" @click="move('add')" class="btn btn-block btn-sm" :disabled="isGroupByEnabled || 0 === selectedColumns.available.length">
            <span class="visible-md visible-lg"><i class="fa fa-angle-right"></i></span>
            <span class="visible-xs visible-sm"><i class="fa fa-angle-down"></i></span>
        </button>
    </div>
    <span class="visible-md visible-lg">&nbsp;</span>
    <div class="col-xs-6 col-md-12">
        <button type="button" @click="move('remove')" class="btn btn-block btn-sm" :disabled="isGroupByEnabled || 0 === selectedColumns.display.length">
            <span class="visible-md visible-lg"><i class="fa fa-angle-left"></i></span>
            <span class="visible-xs visible-sm"><i class="fa fa-angle-up"></i></span>
        </button>
    </div>
  </div>
</template>
<script>
export default {
  name: 'ColumnsMover',
  props: ['selectedColumns', 'isGroupByEnabled'],
  methods: {
    move (action) {
      this.$emit('column-moved', action)
    }
  }
}
</script>
