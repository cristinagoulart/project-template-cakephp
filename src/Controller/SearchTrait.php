<?php
namespace App\Controller;

use Cake\Http\Exception\BadRequestException;

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

        if (! $table->hasBehavior('Searchable')) {
            throw new BadRequestException(sprintf('Search is not available for %s', $table->getAlias()));
        }

        $this->render('/Module/search-new');
    }
}
