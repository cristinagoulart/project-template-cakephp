<?php
namespace App\Test\TestCase\Shell;

use App\Shell\UsersShell;
use Cake\Console\ConsoleIo;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\Stub\ConsoleOutput;
use Cake\TestSuite\TestCase;

/**
 * App\Shell\UsersShell Test Case
 */
class UsersShellTest extends TestCase
{
    /**
     * @var \Cake\TestSuite\Stub\ConsoleOutput
     */
    private $out;

    /**
     * @var \Cake\Console\ConsoleIo
     */
    private $io;

    /**
     * @var \Cake\ORM\Table
     */
    private $Users;

    /**
     * Instance of the Shell
     */
    private $Shell;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.users',
    ];

    /**
     * Set up
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->out = new ConsoleOutput();
        $this->io = new ConsoleIo($this->out);
        $this->Users = TableRegistry::get('CakeDC/Users.Users');
        $this->Shell = $this->getMockBuilder('App\Shell\UsersShell')
            ->setMethods(['_welcome'])
            ->setConstructorArgs([$this->io])
            ->getMock();
        $this->Shell->Users = $this->getMockBuilder('CakeDC\Users\Model\Table\UsersTable')
            ->setMethods(['newEntity', 'save'])
            ->getMock();
    }

    /**
     * Tear Down
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        unset($this->Shell);
    }

    /**
     * Add superuser test
     * Adding superuser with username, email and password
     *
     * @return void
     */
    public function testAddSuperuser(): void
    {
        $data = [
            'username' => 'foo',
            'password' => 'foo',
            'email' => 'foo@example.com',
            'active' => 1
        ];

        $entity = $this->Users->newEntity($data);

        $this->Shell->Users->expects($this->once())
            ->method('newEntity')
            ->with($data)
            ->will($this->returnValue($entity));

        $this->Shell->Users->expects($this->once())
            ->method('save')
            ->with($entity)
            ->will($this->returnValue($entity));

        $this->Shell->runCommand([
            'addSuperuser',
            '--username=' . $data['username'],
            '--password=' . $data['password'],
            '--email=' . $data['email']
        ]);

        // capture output
        $output = $this->out->messages();

        $expected = [
            'Username: ' . $data['username'],
            'Email   : ' . $data['email'],
            'Password: ' . $data['password']
        ];

        foreach ($expected as $param) {
            $this->assertContains($param, join('', $output));
        }
    }
}
