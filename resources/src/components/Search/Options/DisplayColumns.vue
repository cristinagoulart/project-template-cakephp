<template>
    <div>
        <div class="col-md-5 col-lg-4">
            <label for="available-columns">Available Columns</label>
            <select v-model="selectedAvailable"  class="form-control input-sm" multiple size="8">
                <option v-for="column in available" :value="column.field">
                    {{ column.label }}
                    <template v-if="column.group !== model">
                        ({{ column.group }})
                    </template>
                </option>
            </select>
        </div>
        <div class="col-md-2">
            <label>&nbsp;</label>
            <button type="button" @click="addColumn()" id="available-columns_rightSelected" class="btn btn-block btn-xs">
                <i class="glyphicon glyphicon-chevron-right"></i>
            </button>
            <button type="button" @click="removeColumn()" id="available-columns_leftSelected" class="btn btn-block btn-xs">
                <i class="glyphicon glyphicon-chevron-left"></i>
            </button>
        </div>
        <div class="col-md-5 col-lg-4">
            <label for="display-columns">Display Columns</label>
            <select v-model="selectedDisplay" class="form-control input-sm" multiple size="8">
                <option v-for="column in display" :value="column.field">
                    {{ column.label }}
                </option>
            </select>
            <div class="row">
                <div class="col-sm-6">
                    <button type="button" id="available-columns_move_up" class="btn btn-block btn-xs">
                        <i class="glyphicon glyphicon-arrow-up"></i>
                    </button>
                </div>
                <div class="col-sm-6">
                    <button type="button" id="available-columns_move_down" class="btn btn-block btn-xs">
                        <i class="glyphicon glyphicon-arrow-down"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios'

export default {

    props: {
        availableColumns: {
            type: Array,
            required: true
        },
        displayColumns: {
            type: Array,
            required: true
        },
        model: {
            type: String,
            required: true
        }
    },

    data: function () {
        return {
            available: this.availableColumns,
            display: this.displayColumns,
            selectedAvailable: [],
            selectedDisplay: []
        }
    },

    methods: {
        addColumn: function () {
            for( var i = this.available.length; i--;) {
                if (-1 < this.selectedAvailable.indexOf(this.available[i].field)) {
                    this.display.push(
                        this.available.splice(i, 1)[0]
                    )
                }
            }
        },

        removeColumn: function () {
            for( var i = this.display.length; i--;) {
                if (-1 < this.selectedDisplay.indexOf(this.display[i].field)) {
                    this.available.push(
                        this.display.splice(i, 1)[0]
                    )
                }
            }
        }
    }

}
</script>