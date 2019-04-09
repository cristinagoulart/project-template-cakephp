<?php
use Cake\Core\Configure;

/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

echo $this->Html->css('database-logs', ['block' => 'css']);

$typeStyles = Configure::read('DatabaseLog.typeStyles');
$age = Configure::read('DatabaseLog.maxLength');

$pass = $this->request->getParam('pass');
$id = '';
if (!empty($pass)) {
    $id = $pass[0];
}

echo $cakeView->Html->scriptBlock(
    'loadLogsTable(\'/logs/search/'.$id.'\')
    function loadLogsTable(url){
        var dataObj = $("form").serializeArray()
        $.each( dataObj, function( key, obj ) {
            // find field display_columns and get all the options inside
            if (obj.name == "display_columns") {
                $(\'select[name*="display_columns"] > option\').each(function(){
                    var displayColumns = {}
                    displayColumns.name = "display_columns[]"
                    displayColumns.value = $(this).val()
                    dataObj.push( displayColumns )
                });
            }
        })

        $.ajax({
          url: url,
          data: dataObj,
          contentType: "application/json",
        }).done(function(data) {
          $("#advance-log").html(data)
        })
    }

    $(document).on( \'click\', \'.pagination a\', function(e){
        e.preventDefault()
        loadLogsTable($(this).attr(\'href\'))
    })',
    ['block' => 'scriptBottom']
);
?>
<div id="advance-log"></div>