export default {
  methods: {
    getElementBackground (item) {
      let colorClass = 'info'

      if (item.hasOwnProperty('color')) {
        colorClass = item.color
      }

      return 'box-' + colorClass
    },
    getElementIcon (item) {
      let className = 'cube'

      if (item.hasOwnProperty('icon')) {
        className = item.icon
      }

      return 'fa-' + className
    }
  }
}
