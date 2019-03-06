<?php
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
?>
<section class="content-header">
    <h4><?= __('Search') ?></h4>
</section>
<section class="content">
<?php
echo $this->element('Search.Search/filters', [
    'searchOptions' => $searchOptions,
    'searchableFields' => $searchableFields,
    'savedSearch' => $savedSearch,
    'searchData' => $searchData,
    'isEditable' => $isEditable,
    'preSaveId' => $preSaveId,
    'associationLabels' => $associationLabels
]);

$controller = $this->request->getParam('controller');

if ('Logs' === $controller) {
	$cellLocation = 'Log/results';
} else {
	$cellLocation = 'Search.Search/results';
}

echo $this->element($cellLocation, [
    'searchableFields' => $searchableFields,
    'savedSearch' => $savedSearch,
    'searchData' => $searchData,
    'preSaveId' => $preSaveId,
    'associationLabels' => $associationLabels
]);
?>
</section>