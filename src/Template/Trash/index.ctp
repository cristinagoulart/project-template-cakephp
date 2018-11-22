<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4>Trash</h4>
        </div>
    </div>
</section>
<section class="content">
    <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-6">
        	<div class="box box-primary">
                <div class="box-body">
                    <div class="dataTables_wrapper form-inline dt-bootstrap no-footer">
                        <table class="table table-hover table-condensed table-vertical-align dataTable no-footer" width="100%" role="grid">
                            <thead>
                                <tr role="row">
                                    <th>#</th>
                                    <th>Table</th>
                                    <th>In Trash</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach( $table_list as $key => $table_details ): ?>
                                    <tr>
                                        <td><?= $key+1 ?></td>
                                        <td>
                                            <a href='#' data-href="/<?= $table_details['controllerName'] ?>/search" class="view-trash" title="View" target="_self" data-table='<?= $table_details['tableClass'] ?>'>
                                                <?= $table_details['tableName'] ?>
                                            </a>
                                        </td
>                                        <td>
                                            <?= $table_details['total'] ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-xs">
                                                <a href='#' data-href="/<?= $table_details['controllerName'] ?>/search" class="btn btn-default<?php if( !$table_details['total'] ): ?> disabled<?php endif ?> view-trash" title="View" target="_self" data-table='<?= $table_details['tableClass'] ?>'>
                                                    <i class="menu-icon fa fa-eye"></i></a>
                                                <a href="" class="btn <?php if( $table_details['total'] ): ?>btn-danger<?php else: ?>btn-default disabled<?php endif; ?>" onClick="return confirm('Are you sure you want to permanently delete all records from (<?= $table_details['tableName'] ?>)?')" title="Permanently delete all" target="_self"><i class="menu-icon fa fa-trash"></i> </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                        <?php $this->Html->scriptStart(['block' => 'scriptBottom']); ?>
                            (function($) {
                            $('.view-trash').click(function(){
                                var form = $('<form action="' + $(this).attr('data-href') + '" method="POST"><div style="display:none;"><input type="hidden" name="_method" value="POST" /><input type="hidden" name="_csrfToken" value="<?= $this->request->param('_csrfToken') ?>"><input type="hidden"name="criteria[' + $(this).attr('data-table') + '.trashed][0][type]" value="datetime"><select name="criteria[' + $(this).attr('data-table') + '.trashed][0][operator]"><option value="is">is</option><option value="is_not">is not</option><option value="greater" selected="">from</option><option value="less">to</option></select><input type="text" name="criteria[' + $(this).attr('data-table') + '.trashed][0][value]" id="" value="1900-01-01 00:00"></div></form>');
                                $('body').append(form);
                                $(form).submit();

                            })

                        })(jQuery);
                        <?= $this->Html->scriptEnd() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>