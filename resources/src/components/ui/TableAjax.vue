<template>
    <div>
        <div class="row">
            <div class="col-xs-12 text-right">
                <div class="btn-group btn-group-sm">
                <button v-if="! data.group_by && withBatch" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" :disabled="batchButton.disabled" aria-expanded="false"><i class="fa fa-bars"></i> Batch <span class="caret"></span></button>
                <ul class="dropdown-menu">
                    <li><a href="#" @click.prevent="batchEdit()"><i class="fa fa-pencil"></i> Edit</a></li>
                    <li><a href="#" @click.prevent="batchDelete()"><i class="fa fa-trash"></i> Delete</a></li>
                </ul>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-condensed table-vertical-align" width="100%">
                <thead>
                    <tr>
                        <th v-if="isBatchEnabled" class="dt-select-column"></th>
                        <th v-for="header in headers">{{ header.text }}</th>
                        <th v-if="!isGroupByEnabled">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</template>

<script>
import 'datatables.net-bs/css/dataTables.bootstrap.min.css'
import 'datatables.net-select-bs/css/select.bootstrap.min.css'
import * as $ from 'jquery'
import axios from 'axios'
import dataTables from 'datatables.net'
import dataTablesBootstrap from 'datatables.net-bs'
import dataTablesSelect from 'datatables.net-select'
import dataTablesSelectBootstrap from 'datatables.net-select-bs'

export default {
  props: {
    data: {
      type: Object
    },
    headers: {
      type: Array,
      required: true
    },
    model: {
      type: String,
      required: true
    },
    orderDirection: {
      type: String,
      default: 'asc'
    },
    orderField: {
      type: String,
      default: ''
    },
    primaryKey: {
      type: String,
      required: true
    },
    requestType: {
      type: String,
      default: 'GET'
    },
    url: {
      type: String,
      required: true
    },
    withBatch: {
      type: Boolean,
      default: true
    }
  },

  data () {
    return {
      batchButton: {
        disabled: true
      },
      table: {}
    }
  },

  mounted () {
    this.initialize()
  },
  computed: {
    isBatchEnabled () {
      let result = false

      if (!this.data.group_by) {
        result = true
      }

      if (!this.withBatch) {
        result = false
      }

      return result
    },
    isGroupByEnabled () {
      let result = false

      if (this.data.group_by) {
        result = true
      }

      return result
    }
  },
  methods: {
    initialize () {
      const self = this

      let orderColumn = Array.from(this.headers, header => header.value).indexOf(this.orderField)
      // handle out-of-bounds
      orderColumn = -1 === orderColumn ? 0 : orderColumn
      if (!this.data.group_by) {
        orderColumn += 1
      }

      var settings = {
        searching: false,
        lengthMenu: [5, 10, 25, 50, 100],
        pageLength: 10,
        language: { processing: '<i class="fa fa-refresh fa-spin fa-fw"></i> Processing...' },
        order: [[orderColumn, this.orderDirection]],
        // ajax settings
        processing: true,
        serverSide: true,
        deferRender: true,
        ajax: {
          url: this.url,
          type: this.requestType,
          headers: axios.defaults.headers.common,
          data: function (d) {
            let fields = Array.from(self.headers, header => header.value)
            if (!self.isGroupByEnabled) {
                fields.unshift(self.primaryKey)
            }

            let sort = fields[d.order[0].column]

            const data = {
              direction: d.order[0].dir,
              fields: fields,
              limit: d.length,
              page: 1 + d.start / d.length,
              sort: sort
            }

            Object.assign(data, self.data)

            return JSON.stringify(data)
          },
          dataFilter: function (d) {
            d = $.parseJSON(d)

            d.recordsTotal = d.pagination.count
            d.recordsFiltered = d.pagination.count
            d.data = self.dataFormatter(d.data)

            return JSON.stringify(d)
          }
        }
      }

      if (!this.isGroupByEnabled) {
        Object.assign(settings, {
          columnDefs: [{ targets: [-1], orderable: false }]
        })
      }

      if (this.isBatchEnabled) {
        if (!this.isGroupByEnabled) {
          Object.assign(settings, {
            createdRow: function ( row, data, index ) {
              $(row).attr('data-id', data[0])
              $('td', row).eq(0).text('')
            },
            select: {
              style: 'multi',
              selector: 'td:first-child'
            }
          })

          settings.columnDefs[0].targets.push(0)
          settings.columnDefs.push({targets: [0], className: 'select-checkbox'})
        }
      }
      // Fetching alerted errors into callback
      $.fn.dataTable.ext.errMode = function (settings, techNote, message) {
        console.log(message)
      }

      this.table = $(this.$el.querySelector('table')).DataTable(settings)

      if (!this.data.group_by) {
        this.table.on('select', function () {
          self.batchButton.disabled = false
        })

        this.table.on('deselect', function (e, dt, type, indexes) {
          if (null === self.$el.querySelector('table tr.selected')) {
            self.batchButton.disabled = true
          }
        })
      }

      this.table.on('order.dt', function () {
        const order = self.table.order()
        let orderBy = ''
        let orderIndex = order[0][0]
        let orderDirection = order[0][1]

        if (self.isBatchEnabled) {
          orderBy = self.headers[orderIndex - 1].value
        }

        if (self.isGroupByEnabled) {
          orderBy = self.headers[orderIndex].value
        }

        self.$emit('sort-field-updated', orderBy)
        self.$emit('sort-order-updated', orderDirection)
      })

      this.table.on('click', 'a[data-delete="1"]', function(e) {
        e.preventDefault()

        if (! confirm('Are you sure you want to delete this record?')) {
          return
        }

        axios({
          method: 'delete',
          url: $(this).attr('href'),
        }).then(response => {
          if (true === response.data.success) {
            self.table.ajax.reload()
          }
        }).catch(error => console.log(error))
      })

      // select/deselect all table rows
      // @link https://stackoverflow.com/questions/42570465/datatables-select-all-checkbox?answertab=active#tab-top
      this.table.on('click', 'th.select-checkbox', function () {
        let element = $(this)
        if (element.hasClass('selected')) {
          self.table.rows().deselect()
          element.removeClass('selected')
        } else {
          self.table.rows().select()
          element.addClass('selected')
        }
      })

      // check/uncheck select-all checkbox based on rows select/deselect triggering
      // @link https://stackoverflow.com/questions/42570465/datatables-select-all-checkbox?answertab=active#tab-top
      this.table.on('select deselect', function () {
        let element = $(this).find('th.select-checkbox')
        if (self.table.rows({ selected: true }).count() !== self.table.rows().count()) {
          element.removeClass('selected')
        } else {
          element.addClass('selected')
        }
      })
    },

    dataFormatter (data) {
      const result = []

      const combinedColumns = []
      //this.options.ajax.hasOwnProperty('combinedColumns') ? this.options.ajax.combinedColumns : []
      const headers = Array.from(this.headers, header => header.value)
      if (this.isBatchEnabled) {
        if (! this.data.group_by) {
          headers.unshift(this.primaryKey)
        }
      }

      const length = headers.length

      for (const index in data) {
        if (! data.hasOwnProperty(index)) {
          continue
        }

        result[index] = []
        for (let i = 0; i < length; i++) {
          const header = headers[i]
          var value = []

          // normal field
          if (data[index][header]) {
            value.push(data[index][header])
          }

          // combined field
          if (combinedColumns[header]) {
            let length = combinedColumns[header].length
            for (let x = 0; x < len; x++) {
              value.push(data[index][combinedColumns[header][x]])
            }
          }

          result[index].push(value.join(' '))
        }
      }

      if (!this.data.group_by) {
        for (const index in data) {
          if (! data[index].hasOwnProperty('_permissions')) {
            return
          }

          let html = ''

          if (data[index]._permissions.view) {
            html += '<a href="/' + this.model + '/view/' + data[index][this.primaryKey] + '" class="btn btn-default" title="View"><i class="menu-icon fa fa-eye"></i></a>'
          }

          if (data[index]._permissions.edit) {
            html += '<a href="/' + this.model + '/edit/' + data[index][this.primaryKey] + '" class="btn btn-default" title="Edit"><i class="menu-icon fa fa-pencil"></i></a>'
          }

          if (data[index]._permissions.delete) {
            html += '<a href="/api/' + this.model + '/delete/' + data[index][this.primaryKey] + '.json" data-delete="1" class="btn btn-default" title="Delete"><i class="menu-icon fa fa-trash"></i></a>'
          }

          html = '<div class="btn-group btn-group-xs">' + html + '</div>'

          result[index].push(html)
        }
      }

      return result
    },

    /**
     * {@link} https://stackoverflow.com/questions/19064352/how-to-redirect-through-post-method-using-javascript/27766998
     * @return {undefined}
     */
    batchEdit () {
      if (this.data.group_by || !this.withBatch) {
        return
      }

      const form = document.createElement('form')
      document.body.appendChild(form)

      form.method = 'post'
      form.action = '/' + this.model + '/batch/edit'
      this.$el.querySelectorAll('table tr.selected').forEach(function (row) {
        const input = document.createElement('input')
        input.type = 'hidden'
        input.name = 'batch[ids][]'
        input.value = row.getAttribute('data-id')
        form.appendChild(input)
      })

      const input = document.createElement('input')
      input.type = 'hidden'
      input.name = '_csrfToken'
      input.value = axios.defaults.headers.common['X-CSRF-Token']

      form.appendChild(input)

      form.submit()
    },

    batchDelete () {
      if (this.data.group_by || !this.withBatch) {
        return
      }

      if (!confirm('Are you sure you want to delete the selected records?')) {
        return
      }

      const form = document.createElement('form')
      document.body.appendChild(form)

      form.method = 'post'
      form.action = '/' + this.model + '/batch/delete'
      this.$el.querySelectorAll('table tr.selected').forEach(function (row) {
        const input = document.createElement('input')
        input.type = 'hidden'
        input.name = 'batch[ids][]'
        input.value = row.getAttribute('data-id')
        form.appendChild(input)
      })

      const input = document.createElement('input')
      input.type = 'hidden'
      input.name = '_csrfToken'
      input.value = axios.defaults.headers.common['X-CSRF-Token']

      form.appendChild(input)

      form.submit()
    }
  }

}
</script>
<style>
/*
@link https://stackoverflow.com/questions/42570465/datatables-select-all-checkbox?answertab=active#tab-top
*/
table.dataTable thead th.select-checkbox {
    position: relative;
}

table.dataTable thead th.select-checkbox:before,
table.dataTable thead th.select-checkbox:after {
    display: block;
    position: absolute;
    top: 1.2em;
    left: 50%;
    width: 12px;
    height: 12px;
    box-sizing: border-box;
}

table.dataTable thead th.select-checkbox:before {
    content: ' ';
    margin-top: -6px;
    margin-left: -6px;
    border: 1px solid black;
    border-radius: 3px;
}

table.dataTable thead th.select-checkbox.selected::after {
    content: "\2714";
    margin-top: -11px;
    margin-left: -4px;
    text-align: center;
    text-shadow: 1px 1px #B0BED9, -1px -1px #B0BED9, 1px -1px #B0BED9, -1px 1px #B0BED9;
}
</style>
