<template>
  <div>
    <div class="col-xs-6">
        <button type="button" @click="sort('up')" :disabled="isOrderDisabled" class="btn btn-block btn-sm">
            <i class="fa fa-angle-up"></i>
        </button>
    </div>
    <div class="col-xs-6">
        <button type="button" @click="sort('down')" :disabled="isOrderDisabled" class="btn btn-block btn-sm">
            <i class="fa fa-angle-down"></i>
        </button>
    </div>
  </div>
</template>
<script>
export default {
  name: 'ColumnsSorter',
  props: ['isOrderDisabled'],
  methods: {
    sort (direction) {
      this.$emit('display-columns-sorted', direction)
    }
  }
}
</script>
