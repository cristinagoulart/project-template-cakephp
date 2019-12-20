<?php

namespace App\Controller\Traits;

use App\Service\Export;
use Cake\ORM\Table;
use Webmozart\Assert\Assert;

trait ExportTrait
{
    /**
     * Method responsible for exporting entities into a CSV file and forcing file download.
     *
     * @return \Cake\Http\Response|void
     */
    public function export()
    {
        $this->getRequest()->allowMethod('POST');

        $table = $this->loadModel();
        Assert::isInstanceOf($table, Table::class);

        $export = Export::fromIds(
            (array)$this->getRequest()->getData('ids'),
            $table,
            (array)$this->getRequest()->getData('headers'),
            (bool)$this->getRequest()->getData('formatted', false)
        );

        $this->set('url', $export->url());
        $this->set('_serialize', 'url');
    }
}
