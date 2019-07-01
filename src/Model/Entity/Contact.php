<?php
namespace App\Model\Entity;

use BadMethodCallException;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use CsvMigrations\Exception\UnsupportedPrimaryKeyException;
use Webmozart\Assert\Assert;

/**
 * Contact Entity.
 */
class Contact extends Entity
{
    /**
     * @var string[] $_virtual - make virtual fields visible to export to JSON or array
     */
    protected $_virtual = ['name'];

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];

    /**
     * Virtual Field: name
     *
     * @return string
     */
    protected function _getName(): string
    {
        $name = $this->get('first_name') . ' ' . $this->get('last_name');

        $table = TableRegistry::get('Contacts');

        $primaryKey = $table->getPrimaryKey();
        if (!is_string($primaryKey)) {
            throw new UnsupportedPrimaryKeyException();
        }

        $options = [
            'conditions' => [$primaryKey => $this->get('id')],
            'limit' => 1
        ];
        // try to fetch with trashed if finder method exists, otherwise fallback to find all
        try {
            $query = $table->find('withTrashed', $options);
        } catch (BadMethodCallException $e) {
            $query = $table->find('all', $options);
        }

        try {
            $record = $query->firstOrFail();
        } catch (RecordNotFoundException $e) {
            return $name;
        }

        Assert::isInstanceOf($record, EntityInterface::class);
        if ('organization' === $record->get('type')) {
            $name = $this->get('company_name');
        }

        return (string)$name;
    }
}
