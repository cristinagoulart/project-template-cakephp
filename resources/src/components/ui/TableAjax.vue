<template>
    <div>
        <div class="row">
            <div class="col-xs-12 text-right">
                <div v-if="isBatchEnabled" class="btn-group btn-group-sm">
                <button type="button" :disabled="!selected.length" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-bars"></i> Actions <span class="caret"></span></button>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li v-if="withBatchEdit"><a href="#" @click.prevent="batchEdit()"><i class="fa fa-pencil"></i> Edit</a></li>
                    <li v-if="withBatchDelete"><a href="#" @click.prevent="batchDelete()"><i class="fa fa-trash"></i> Delete</a></li>
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
                        <th v-if="withActions">Actions</th>
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
      default: 'DESC'
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
    withActions: {
      type: Boolean,
      default: true
    },
    withBatchDelete: {
      type: Boolean,
      default: true
    },
    withBatchEdit: {
      type: Boolean,
      default: true
    }
  },

  data () {
    return {
      selected: []
    }
  },

  mounted () {
    this.initialize()
  },
  computed: {
    isBatchEnabled () {
      return this.withBatchEdit || this.withBatchDelete
    }
  },
  methods: {
    initialize () {
      const self = this

      let orderColumn = Array.from(this.headers, header => header.value).indexOf(this.orderField)
      // handle out-of-bounds
      orderColumn = -1 === orderColumn ? 0 : orderColumn
      // shift order column by one, since batch column is prepended
      if (this.isBatchEnabled) {
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
            if (self.isBatchEnabled) {
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

      if (this.isBatchEnabled) {
        Object.assign(settings, {
          // disable sorting by first and last columns (select, actions)
          columnDefs: [
            { targets: [0, -1], orderable: false },
            { targets: [0], className: 'select-checkbox' }
          ],
          // add first column with hidden record id, for batch selection
          createdRow: function (row, data, index) {
            $(row).attr('data-id', data[0])
            $('td', row).eq(0).text('')
          },
          // enable select functionality on first column
          select: { style: 'multi', selector: 'td:first-child' }
        })
      }

      // Fetching alerted errors into callback
      $.fn.dataTable.ext.errMode = function (settings, techNote, message) {
        console.log(message)
      }

      const table = $(this.$el.querySelector('table')).DataTable(settings)

      table.on('order.dt', function () {
        const order = table.order()
        const orderBy = self.isBatchEnabled ?
          (self.headers.length ? self.headers[order[0][0] - 1].value : '') :
          self.headers[order[0][0]].value

        self.$emit('sort-field-updated', orderBy)
        self.$emit('sort-order-updated', order[0][1])
      })

      table.on('click', 'a[data-delete="1"]', function(e) {
        e.preventDefault()

        if (! confirm('Are you sure you want to delete this record?')) {
          return
        }

        axios({
          method: 'delete',
          url: $(this).attr('href'),
        }).then(response => {
          if (true === response.data.success) {
            table.ajax.reload()
          }
        }).catch(error => console.log(error))
      })

      // select/deselect all table rows
      // @link https://stackoverflow.com/questions/42570465/datatables-select-all-checkbox?answertab=active#tab-top
      table.on('click', 'th.select-checkbox', function () {
        let element = $(this)
        if (element.hasClass('selected')) {
          table.rows().deselect()
          element.removeClass('selected')
        } else {
          table.rows().select()
          element.addClass('selected')
        }

        self.batchSetSelected()
      })

      // check/uncheck select-all checkbox based on rows select/deselect triggering
      // @link https://stackoverflow.com/questions/42570465/datatables-select-all-checkbox?answertab=active#tab-top
      table.on('select deselect', function () {
        let element = $(this).find('th.select-checkbox')
        if (table.rows({ selected: true }).count() !== table.rows().count()) {
          element.removeClass('selected')
        } else {
          element.addClass('selected')
        }

        self.batchSetSelected()
      })
    },

    dataFormatter (data) {
      const result = []

      const combinedColumns = []
      const headers = Array.from(this.headers, header => header.value)
      if (this.isBatchEnabled) {
        headers.unshift(this.primaryKey)
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

      // create action buttons for each record
      if (this.withActions) {
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

    batchSetSelected () {
      const self = this
      // reset batch selected IDs
      this.selected = []
      this.$el.querySelectorAll('table tr.selected').forEach(function (row) {
        self.selected.push(row.getAttribute('data-id'))
      })
    },

    /**
     * {@link} https://stackoverflow.com/questions/19064352/how-to-redirect-through-post-method-using-javascript/27766998
     * @return {undefined}
     */
    batchEdit () {
      if (!this.withBatchEdit || !this.selected.length) {
        return
      }

      this.generateBatchForm('/' + this.model + '/batch/' + 'edit').submit()
    },

    batchDelete () {
      if (!this.withBatchDelete || !this.selected.length) {
        return
      }

      if (!confirm('Are you sure you want to delete the selected records?')) {
        return
      }

      this.generateBatchForm('/' + this.model + '/batch/' + 'delete').submit()
    },

    generateBatchForm (action) {
      const form = document.createElement('form')
      document.body.appendChild(form)

      form.method = 'post'
      form.action = action
      this.selected.forEach(function (id) {
        const input = document.createElement('input')
        input.type = 'hidden'
        input.name = 'batch[ids][]'
        input.value = id
        form.appendChild(input)
      })

      const input = document.createElement('input')
      input.type = 'hidden'
      input.name = '_csrfToken'
      input.value = axios.defaults.headers.common['X-CSRF-Token']

      form.appendChild(input)

      return form
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
