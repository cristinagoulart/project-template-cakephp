<?php
use Cake\Core\Configure;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

echo $this->Html->css('Qobo/Utils./plugins/datatables/css/dataTables.bootstrap.min', ['block' => 'css']);

echo $this->Html->script(
    [
        'Qobo/Utils./plugins/datatables/datatables.min',
        'Qobo/Utils./plugins/datatables/js/dataTables.bootstrap.min'
    ],
    ['block' => 'scriptBottom']
);

$this->Html->scriptStart(array('block' => 'scriptBottom', 'inline' => false)); ?>

$(document).ready(function(){

    let data = (<?= json_encode($data) ?>)
    console.log(data)

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
                addFieldButton(tab,col,section)   
            })
            addSectionButton(tab,col)
        })
        addColButtun(tab)
    })

    //active the first tab and coloum
    $('#list_tabs li:nth-child(3)').addClass('active');
    $('.tab-pane:nth-child(2)').addClass('active');


    function genTab(tab){
        let idTab = tab.replace(/ |_/g,"_")
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
        let idCol = tab.replace(/ |_/g,"_") +"-"+ col.replace(/ |_/g,"_")
        let new_col_link = `<li><a href="#`+ idCol +`" data-toggle="tab">`+ col +`</a></li>`
        $('#col_'+ tab.replace(/ |_/g,"_")).append(new_col_link)

        let new_col_tab = `<div class="tab-pane" id="`+ idCol +`"></div>`
        $('#section_'+ tab.replace(/ |_/g,"_")).append(new_col_tab)
    }

    function genSection(tab,col,section){
        let idSection = tab.replace(/ |_/g,"_") +"-"+ col.replace(/ |_/g,"_") +"-"+ section.replace(/ |_/g,"_")
        let new_section = `<div class="box box-primary" id="`+ idSection +`">
                                   <div class="box-header">
                                       <h3 class="box-title">`+ section +`</h3>
                                   </div>
                           </div>`
        $('#'+ tab.replace(/ |_/g,"_") +"-"+ col.replace(/ |_/g,"_") ).append(new_section)
    }

    function genField(tab,col,section,key,value){
            let idField = tab.replace(/ |_/g,"_") +"-"+ col.replace(/ |_/g,"_") +"-"+ section.replace(/ |_/g,"_")
            let new_field = `<div class="box-body">
                                <div class="form-group input text">
                                    <label for="settings-theme-title">`+ key +`</label>
                                    <label for="settings-theme-title">`+ value.alias +`</label>
                                </div>
                            </div>`
            $('#'+ idField ).append(new_field)
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
        $('#col_'+ tab.replace(/ |_/g,"_")).append(addCol)
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
        $('#'+ tab.replace(/ |_/g,"_") +"-"+ col.replace(/ |_/g,"_")).append(addSection)
    }

    function addFieldButton(tab,col,section){
        let addField = `<div class="box-body">
                            <form class="addField">
                                <!-- <input type="text" placeholder="Add Field"> -->
                                <button type="submit" class="btn btn-primary">
                                    Add field
                                    <i class="menu-icon fa fa-plus-circle"></i>
                                </button>
                            </form>
                          </div>`

        $('#'+ tab.replace(/ |_/g,"_") +"-"+ col.replace(/ |_/g,"_") +"-"+ section.replace(/ |_/g,"_")).append(addField)
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
        let id = $(this).parents().parents().attr('id').split('-')
        let section = $(this).find("input:text").val()
        genSection(id[0],id[1],section)
        $(this).find("input:text").val('')
    })

    $(document).on('submit','.addField',function(event){
        console.log($("#dataSettings").closest('.box-primary').html())
        event.preventDefault()
        $(this).find('button').text('Add the selected field')
        $(this).removeClass('addField').addClass('AddSelectedFields')
        let id = $(this).parents().parents().attr('id')
        let search = `<div class="box box-primary">
                          <div class="box-body">
                              <table id='dataSettings' class="table table-hover table-condensed table-vertical-align table-datatable">
                                  <thead>
                                  <tr>
                                      <th><?= __('alias') ?></th>
                                      <th><?= __('type') ?></th>
                                      <th><?= __('help') ?></th>
                                      <th><?= __('roles') ?></th>
                                  </tr>
                                  </thead>
                                  <tbody>
                                  <?php foreach ($alldata as $key => $value) : 
                                      if( is_array($value) || is_object($value)){
                                          continue;
                                      }
                                  ?>
                                      <tr>
                                          <td class="alias"><?= $key ?></td>
                                          <td class="type"><?= gettype($value) ?></td>
                                          <td class="help"><input type="text" placeholder="write the help here"></td>
                                          <td class="roles">
                                             <?php 
                                                // to improve ...
                                                $r = '';
                                                foreach($roles as $key){
                                                    $r = $r . $key. ',';
                                                }
                                             ?>
                                           <input type="text" value="<?= substr($r, 0, -1) ?>">                                   
                                          </td>
                                      </tr>
                                  <?php endforeach; ?>
                                  </tbody>
                              </table>
                          </div>
                      </div>`
        $('#' + id).append(search)
        $("#dataSettings").DataTable({
            stateSave:true,
            paging:true,
            searching:true,
            select: {
                        style: 'multi',
                    }
            })
    })

    $(document).on('click','.AddSelectedFields',function(){
            let data = $('#dataSettings .selected')
            $.each(data,function(index,value){
                value = $(value)[0]
                let alias = $(value).find('.alias').text()
                let type = $(value).find('.type').text()
                let help = $(value).find('.help input').val()
                let roles = $(value).find('.roles input').val()
                
                let mydata = {
                    'alias' : alias,
                    'type' : type,
                    'help' : help,
                    'roles' : roles,
                }
                console.log(mydata)
                // let mypath = 
            })
                console.log($(this).parents().parents().attr('id'))
        })

    function addToArray(path,data){

    }


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
</section>