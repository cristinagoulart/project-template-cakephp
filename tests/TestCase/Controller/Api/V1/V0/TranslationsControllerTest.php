<?php
namespace App\Test\TestCase\Controller\Api\V1\V0;

use App\Event\Controller\Api\IndexActionListener;
use Cake\Core\Configure;
use Cake\Event\EventList;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;
use Cake\Utility\Security;
use Firebase\JWT\JWT;

/**
 * Translations\Controller\TranslationsController Test Case
 */
class TranslationsControllerTest extends IntegrationTestCase
{
    /**
     * @var object $Translations
     */
    private $Translations;

    public $fixtures = [
        'plugin.translations.languages',
        'plugin.translations.language_translations',
        'plugin.CakeDC/Users.users',
    ];

    public function setUp()
    {
        parent::setUp();

        $token = JWT::encode(
            ['sub' => '00000000-0000-0000-0000-000000000002', 'exp' => time() + 604800],
            Security::getSalt()
        );

        $this->Translations = TableRegistry::get('Translations.Translations');

        // enable event tracking
        $this->Translations->getEventManager()->setEventList(new EventList());

        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'authorization' => 'Bearer ' . $token
            ]
        ]);

        // Load default plugin configuration
        Configure::load('Translations.translations');

        EventManager::instance()->on(new IndexActionListener());
    }

    public function tearDown()
    {
        unset($this->Translations);

        parent::tearDown();
    }

    public function testIndex(): void
    {
        $this->get('/api/language-translations');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode($this->_response->body());
        $this->assertTrue($response->success);
        $this->assertEmpty($response->data);
    }

    public function testIndexWithModelAndKey(): void
    {
        $this->get('/api/language-translations?object_model=Leads&object_foreign_key=00000000-0000-0000-0000-100000000001');

        $this->assertResponseOk();

        $response = json_decode($this->_response->body());
        $this->assertEquals(3, count($response->data));
    }

    public function testIndexWithField(): void
    {
        $this->get('/api/language-translations?object_model=Leads&object_foreign_key=00000000-0000-0000-0000-100000000001&object_field=description');

        $this->assertResponseOk();

        $response = json_decode($this->_response->getBody());
        $this->assertEquals(2, count($response->data));
    }

    public function testIndexWithLanguage(): void
    {
        $this->get('/api/language-translations?object_model=Leads&object_foreign_key=00000000-0000-0000-0000-100000000001&language=ru');

        $this->assertResponseOk();

        $response = json_decode($this->_response->body());
        $this->assertEquals(2, count($response->data));
    }

    public function testIndexWithFieldAndLanguage(): void
    {
        $this->get('/api/language-translations?object_model=Leads&object_foreign_key=00000000-0000-0000-0000-100000000001&object_field=code&language=ru');

        $this->assertResponseOk();

        $response = json_decode($this->_response->body());
        $this->assertEquals(1, count($response->data));
    }
}
