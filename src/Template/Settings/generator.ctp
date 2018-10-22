<?php
use Cake\Core\Configure;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$fhf = new FieldHandlerFactory($this);

echo $this->Html->css('Qobo/Utils./plugins/datatables/css/dataTables.bootstrap.min', ['block' => 'css']);

echo $this->Html->script(
    [
        'Qobo/Utils./plugins/datatables/datatables.min',
        'Qobo/Utils./plugins/datatables/js/dataTables.bootstrap.min'
    ],
    ['block' => 'scriptBottom']
);

echo $this->Html->scriptBlock(
    '$(".table-datatable").DataTable({
        stateSave:true,
        paging:true,
        searching:true,
        select: {
            style: \'multi\',
        }
    });',
    ['block' => 'scriptBottom']
);

$this->Html->scriptStart(array('block' => 'scriptBottom', 'inline' => false)); ?>

$(document).ready(function(){
    $('#dataSettings').css({"display" : "none"})

    let data = (<?= json_encode($data) ?>)

    // Tabs
    $.each(data,function(tab, cols){
        genTab(tab)
        // Cols
        $.each(cols,function(col,sections){
            genColumn(tab,col)
            // Sections
            $.each(sections,function(section,field){
                genSection(tab,col,section)
                // Fields
                $.each(field,function(key,value){
                    genField(tab,col,section,key,value)
                })   
            })
            addSectionButton(tab,col)
        })
        addColButtun(tab)
    })

    function genTab(tab){
        let idTab = tab.replace(/ /g,"_")
        let new_tab = `<li><a href="#tab`+ idTab  +`" data-tab="`+ idTab  +`" data-toggle="tab" aria-expanded="true">`+ tab +`</a></li>`
        let new_tab_panel =`<div class="tab-pane" id="tab`+ idTab  +`">
                                <div class="row">
                                    <div class="col-md-3">
                                        <ul id="col_`+ idTab  +`" class="nav nav-pills nav-stacked">
                                        </ul>
                                    </div>
                                    <div class="tab-content col-md-9" id="section_`+ idTab  +`">
                                    </div>
                                </div>
                            </div>`

        $('#list_tabs').append(new_tab)
        $('#list_tabs_pane').append(new_tab_panel)
    }

    function genColumn(tab,col){
        let idCol = '['+tab.replace(/ /g,"_") +'][' + col.replace(/ /g,"_")+ ']'
        let new_col_link = `<li><a href="#A`+ idCol +`" data-toggle="tab">`+ col +`</a></li>`
        $('#col_'+ tab.replace(/ /g,"_")).append(new_col_link)

        let new_col_tab = `<div class="tab-pane" id="A`+ idCol +`"></div>`
        $('#section_'+ tab.replace(/ /g,"_")).append(new_col_tab)
    }

    function genSection(tab,col,section){
        let idSection = '['+tab.replace(/ /g,"_") +'][' + col.replace(/ /g,"_") +'][' + section.replace(/ /g,"_")+ ']'
        let new_section = `<div class="box box-primary" id="A`+ idSection +`">
                                   <div class="box-header">
                                       <h3 class="box-title">`+ section +`</h3>
                                   </div>
                           </div>`
        $('#A['+ tab.replace(/ /g,"_") +'][' + col.replace(/ /g,"_") + ']').append(new_section)
    }

    function genField(tab,col,section,key,value){
            tab = '['+tab.replace(/ /g,"_")+']'
            col = '['+col.replace(/ /g,"_")+']'
            section = '['+section.replace(/ /g,"_")+']'
            let new_field = `<div class="box-body">
                                <div class="form-group input text">
                                    <label for="settings-theme-title">`+ key +`</label>
                                    <label for="settings-theme-title">`+ value.alias +`</label>
                                </div>
                            </div>`
            $('#A'+ tab + col + section ).append(new_field)
    }

    function addColButtun(tab){
        let addCol = `<li>
                        <form class="addCol">
                            <input type="text" placeholder="Add Column">
                            <button type="submit" class="btn btn-primary">
                                <i class="menu-icon fa fa-plus-circle"></i>
                            </button>
                        </form>
                      </li>`
        $('#col_'+ tab.replace(/ /g,"_")).append(addCol)
    }

    function addSectionButton(tab,col){
        let addSection = `<div class="box-body">
                            <form class="addSection">
                                <input type="text" placeholder="Add Section">
                                <button type="submit" class="btn btn-primary">
                                    <i class="menu-icon fa fa-plus-circle"></i>
                                </button>
                            </form>
                          </div>`
        $('#A['+ tab.replace(/ /g,"_") +'][' + col.replace(/ /g,"_") +']').append(addSection)
    }

    function genNewColumn(tab,col){
        genColumn(tab,col)
        addSectionButton(tab,col)
    }

    $("#addTab").on('submit',function(event){
        event.preventDefault()
        genTab($("#addTab input:text").val())
        addColButtun($("#addTab input:text").val())
        $("#addTab input:text").val('')
    })

    $(document).on('submit','.addCol',function(event){
        event.preventDefault()
        let tab = $(this).parents().parents().attr('id')
        let col = $(this).find("input:text").val()
        genNewColumn(tab.replace('col_',''),col)
        $(this).find("input:text").val('')
    })

    $(document).on('submit','.addSection',function(event){
        event.preventDefault()
        let tab = $(this).parents().parents().attr('id')
        console.log(tab)
        //let col = $(this).find("input:text").val()
        //genNewColumn(tab.replace('col_',''),col)
        //$(this).find("input:text").val('')
    })


});


<?php $this->Html->scriptEnd(); ?>
<style type="text/css">
   table.dataTable tbody tr.selected {
    color: blue;
    background-color: #c0c0c0;
}

</style>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul id='list_tabs' class="nav nav-tabs">
                 <li class="box-header pull-right">
                    <form id="addTab">
                        <input type="text"  placeholder="Add Tab">
                        <button type="submit" class="btn btn-primary">
                            <i class="menu-icon fa fa-plus-circle"></i>
                        </button>
                    </form>
                 </li>
                </ul>
                <div id='list_tabs_pane' class="tab-content"></div>
            </div>
        </div>
    </div>
    
    <div id='dataSettings' class="box box-primary">
        <div class="box-body">
            <table class="table table-hover table-condensed table-vertical-align table-datatable">
                <thead>
                <tr>
                    <th><?= __('key') ?></th>
                    <th><?= __('value') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($alldata as $key => $value) : 
                    if( is_array($value) || is_object($value)){
                        continue;
                    }
                ?>
                    
                    <tr>
                        <td><?= h($key) ?></td>
                        <td><?= h($value) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>