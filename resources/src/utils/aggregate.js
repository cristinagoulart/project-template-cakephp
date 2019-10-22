const AGGREGATE_PATTERN = /(.*?)\((.*?)\)/

export default {
  extractAggregateField(field) {
    const aggregate = field.match(AGGREGATE_PATTERN)

    return aggregate !== null ? aggregate[2] : ''
  },
  extractAggregateType(field) {
    const aggregate = field.match(AGGREGATE_PATTERN)

    return aggregate !== null ? aggregate[1] : ''
  },
  getAggregate(fields) {
    if (!this.hasAggregate(fields)) {
      return ''
    }
    return fields.find(item => this.isAggregate(item))
  },
  getExpression(type, field) {
    return type + '(' + field + ')'
  },
  hasAggregate(fields) {
    return fields.find(item => this.isAggregate(item)) !== undefined
  },
  isAggregate(field) {
      return field.match(AGGREGATE_PATTERN) !== null
  }
}
