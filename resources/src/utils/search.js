export const API_STORE_SEARCH = '/search/saved-searches/add'
export const API_EDIT_SEARCH = '/search/saved-searches/edit'
export const API_VIEW_SEARCH = '/search/saved-searches/view'
export const API_DELETE_SEARCH = '/search/saved-searches/delete'
export const API_LIST_SEARCHES = '/search/saved-searches/index'

export const FIELD_TYPE_MAP = {
  blob: 'text',
  boolean: 'boolean',
  country: 'boolean',
  currency: 'boolean',
  date: 'number',
  datetime: 'number',
  dblist: 'boolean',
  decimal: 'number',
  email: 'text',
  integer: 'number',
  list: 'boolean',
  phone: 'text',
  related: 'boolean',
  reminder: 'number',
  string: 'text',
  sublist: 'boolean',
  text: 'text',
  time: 'number',
  url: 'text'
}

export const AGGREGATES = [
  { text: 'Average', value: 'AVG' },
  { text: 'Count', value: 'COUNT' },
  { text: 'Maximum', value: 'MAX' },
  { text: 'Minimum', value: 'MIN' },
  { text: 'Sum', value: 'SUM' }
]

export const CONJUNCTIONS = [
    { text: 'Match all filters', value: 'AND' },
    { text: 'Match any filter', value: 'OR' }
]

export const FIELDS_BASIC_TYPES = [
  'string',
  'text',
  'textarea',
  'related',
  'email',
  'url',
  'phone',
  'integer'
]

export const FIELD_OPERATOR_TYPES = {
  boolean: [
    { value: 'is', text: 'is' },
    { value: 'is_not', text: 'is not' }
  ],
  number: [
    { value: 'is', text: 'is' },
    { value: 'is_not', text: 'is not' },
    { value: 'greater', text: 'greater' },
    { value: 'less', text: 'less' }
  ],
  text: [
    { value: 'contains', text: 'contains' },
    { value: 'not_contains', text: 'does not contain' },
    { value: 'starts_with', text: 'starts with' },
    { value: 'ends_with', text: 'ends with' }
  ]
}
