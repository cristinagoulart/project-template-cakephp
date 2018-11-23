<?php
namespace App\Test\TestCase\Event;

use App\Event\Model\LookupListener;
use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class LookupListenerTest extends TestCase
{
    private $table;

    public $fixtures = [
        'app.users',
    ];

    public function setUp()
    {
        parent::setUp();

        $this->table = TableRegistry::get('Users');
    }

    public function tearDown()
    {
        unset($this->table);

        parent::tearDown();
    }

    public function testBeforeFind(): void
    {
        $event = new Event('Model.beforeFind', $this->table);
        $query = $this->table->find('all')->where(['id' => 'user-1@test.com']);
        $options = new ArrayObject(['lookup' => true, 'value' => 'user-1@test.com']);
        $primary = true;

        $listener = new LookupListener();

        $listener->beforeFind($event, $query, $options, $primary);

        $this->assertSame('user-1@test.com', $query->firstOrFail()->get('email'));
    }
}
