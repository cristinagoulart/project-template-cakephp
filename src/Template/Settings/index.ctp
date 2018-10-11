<?php
use Cake\Core\Configure;
use Cake\Utility\Hash;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;


/**
 *	The structure is :
 *	tab.column.section.fieldname => value
 * 
 */

$fhf = new FieldHandlerFactory($this);
$data = Configure::read('Settings');
?>

<?php $this->Html->scriptStart(array('block' => 'scriptBottom', 'inline' => false)); ?>

$(document).ready(function(){
	$('#settings-search').on('input',function(){
		let search = $(this).val();
	})
});

<?php $this->Html->scriptEnd(); ?>

<section class="content-header">
	<h1><?= __('Settings'); ?></h1>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="nav-tabs-custom">
				<ul class="nav nav-tabs">
					<?php
					// Tab
					$tabs = array_keys($data);
					$first = true;
					foreach ($tabs as $tab) {
						$id_tab = str_replace(' ','_',$tab);
						echo $first ? '<li class="active">' : '<li>';
						echo '<a href="#' . $id_tab . '" data-tab="' . $id_tab . '" data-toggle="tab" aria-expanded="true">';
						echo $tab;
						echo '</a>';
						echo '</li>';
						$first = false;
					}
					?>
					<li>
						<form class="navbar-form navbar-right" role="search">
							<div class="form-group">
								<input type="text" class="form-control" id="settings-search" placeholder="Search">
							</div>
						</form>
					</li>
				</ul>
				<div class="tab-content">
					<?php
					$first = true;
					foreach ($tabs as $tab) {
						$id_tab = str_replace(' ','_',$tab);
						echo $first ? '<div class="tab-pane active" id="' . $id_tab . '">' : '<div class="tab-pane" id="' . $id_tab . '">';
						?>
						<div class="row">
							<div class="col-md-3">
								<ul class="nav nav-pills nav-stacked">
									<?php
									$columns = array_keys($data[$tab]);

									$first = true;
									foreach ($columns as $column) {
										$active = $first ? 'class="active"' : '';
										$id_column = str_replace(' ','_',$column);
										echo '<li '. $active .'><a href="#'. $id_column .'" data-toggle="tab">'. $column .'</a></li>';
										$first = false;
									}
									?>
								</ul>
							</div>
							<div class="tab-content col-md-9">
								<?php
								$columns = array_keys($data[$tab]);
								$first = true;
								// Columns
								foreach ($columns as $column) {
									$active = $first ? 'active' : '';
									$id_column = str_replace(' ','_',$column);
									echo '<div class="tab-pane '. $active .'" id="'. $id_column .'">';
									// Section
									$sections = array_keys($data[$tab][$column]);
									foreach ($sections as $section) {
										echo '<div class="box box-primary">';
										echo '<div class="box-header">';
										echo '<h3 class="box-title">'. $section .'</h3>';
										echo '</div>';
										echo '<div class="box-body">';
										// Fields
										$fields = $data[$tab][$column][$section];
										foreach ($fields as $field => $fieldValue) {
											$alias = $fieldValue['alias'];
											$type = $fieldValue['type'];
											$value = Configure::read($alias);
											$definition = [
												'name' => $field,
												'type' => $type,
												'required' => true,
											];
											$inputField = $fhf->renderInput($field, $field, $value, ['fieldDefinitions' => $definition]);
											echo $inputField;
											if(isset($fieldValue['help']) ){
												echo '<span class="help-block">'. $fieldValue['help'] .'</span>';
											}
										}
										echo '</div>';
										echo '</div>';
									}

									echo '</div>';
									$first = false;
								}
								?>

							</div>
						</div>

						<?php

						echo '</div>';
						$first = false;
					}
					?>
				</div>
			</div>
		</div>
	</div>
</section>