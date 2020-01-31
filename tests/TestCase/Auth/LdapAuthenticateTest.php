<?php

namespace App\Test\TestCase\Auth;

use App\Auth\LdapAuthenticate;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;

class LdapAuthenticateTest extends TestCase
{
    private $controller;
    private $registry;

    public function setUp(): void
    {
        parent::setUp();

        $request = new ServerRequest();
        $response = new Response();

        $this->controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setMethods(null)
            ->setConstructorArgs([$request, $response])
            ->getMock();
        \Webmozart\Assert\Assert::isInstanceOf($this->controller, \Cake\Controller\Controller::class);
        $this->registry = new ComponentRegistry($this->controller);
    }

    public function tearDown(): void
    {
        unset($this->registry);
        unset($this->controller);

        parent::tearDown();
    }

    public function testConstructor(): void
    {
        $host = 'foobar';
        Configure::write('Ldap.host', $host);
        Configure::write('Ldap.version', null);
        Configure::write('Ldap.port', null);
        $ldapAuthentication = new LdapAuthenticate($this->registry);

        $this->assertSame($host, $ldapAuthentication->getConfig('host'));
        $this->assertSame(3, $ldapAuthentication->getConfig('version'));
        $this->assertSame(389, $ldapAuthentication->getConfig('port'));
    }

    public function testConstructorWithoutHost(): void
    {
        $this->expectException(\Cake\Http\Exception\InternalErrorException::class);

        new LdapAuthenticate($this->registry);
    }

    public function testAuthenticate(): void
    {
        $data = ['username' => 'foo', 'password' => 'bar'];

        Configure::write('Ldap.host', 'foobar');
        $ldapAuthentication = new LdapAuthenticate($this->registry);

        $result = $ldapAuthentication->authenticate(
            new ServerRequest(['post' => $data]),
            new Response()
        );

        $this->assertFalse($result);
    }

    public function testGetUserWithoutUsername(): void
    {
        $data = ['password' => 'bar'];

        Configure::write('Ldap.host', 'foobar');
        $ldapAuthentication = new LdapAuthenticate($this->registry);

        $this->assertFalse($ldapAuthentication->getUser(new ServerRequest(['post' => $data])));
    }

    public function testGetUserWithoutPassword(): void
    {
        $data = ['username' => 'foo'];

        Configure::write('Ldap.host', 'foobar');
        $ldapAuthentication = new LdapAuthenticate($this->registry);

        $this->assertFalse($ldapAuthentication->getUser(new ServerRequest(['post' => $data])));
    }
}
