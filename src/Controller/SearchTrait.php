<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;
use Cake\ORM\Table;
use Cake\Utility\Hash;
use Search\Utility\Export;
use Webmozart\Assert\Assert;

trait SearchTrait
{
    /**
     * Search action
     *
     * @param string $id Saved search id
     * @return \Cake\Http\Response|void|null
     */
    public function search(string $id = '')
    {
        $table = $this->loadModel();
        Assert::isInstanceOf($table, Table::class);

        if (! $table->hasBehavior('Searchable')) {
            throw new BadRequestException(sprintf('Search is not available for %s', $table->getAlias()));
        }

        $this->set('searchId', $id);

        $this->render('/Module/search');
    }

    /**
     * Export Search results
     *
     * Method responsible for exporting search results
     * into a CSV file and forcing file download.
     *
     * @param string $id Saved search id
     * @param string $filename Export filename
     * @return \Cake\Http\Response|void
     */
    public function exportSearch(string $id, string $filename)
    {
        $filename = '' === trim($filename) ? $this->name : $filename;
        $export = new Export($id, $filename, $this->Auth->user());

        if ($this->request->is('ajax') && $this->request->accepts('application/json')) {
            $page = (int)Hash::get($this->request->getQueryParams(), 'page', 1);
            $limit = (int)Hash::get($this->request->getQueryParams(), 'limit', Configure::read('Search.export.limit'));

            $export->execute($page, $limit);

            $result = [
                'success' => true,
                'data' => ['path' => $export->getUrl()],
                '_serialize' => ['success', 'data']
            ];

            $this->set($result);

            return;
        }

        $this->set('count', $export->count());
        $this->set('filename', $filename);
        $this->render('Search.Search/export');
    }
}
