<?php
namespace App\Test\External;

use Cake\Http\Client;
use Cake\Http\Client\Response;
use Cake\TestSuite\IntegrationTestCase;

/**
 * Users External API Test Case
 *
 * @group external
 */
class UsersExternalApiTest extends IntegrationTestCase
{
    public $fixtures = [
        'app.users',
    ];

    /**
     * External API Client object
     *
     * @var \Cake\Http\Client for external api calls.
     */
    protected $apiClient = null;

    public function setUp()
    {
        parent::setUp();

        $this->apiClient = new Client([
            'host' => 'localhost:8000',
            'scheme' => 'http',
        ]);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testExternalApiUsersCRUD(): void
    {
        $response = $this->apiClient->post(
            '/api/users/token.json',
            [
                'username' => getenv('DEV_USER'),
                'password' => getenv('DEV_USER'),
            ],
            [
                'type' => 'json'
            ]
        );

        $this->assertTrue($response->isOk(), "Couldn't fetch API token from default getenv(DEV_USER) user");

        $responseBody = json_decode($response->body(), true);
        unset($response);

        $token = $responseBody['data']['token'];
        $response = $this->sendAuthPost('/api/users/index.json', [], ['token' => $token]);
        $this->assertTrue($response->isOk(), "Couldn't get users from index view");

        $usersResponse = json_decode($response->body(), true);

        $this->assertTrue($usersResponse['success'], "Failed response on API users index call");
        $this->assertNotEmpty($usersResponse['data'], "No users found in API /users/index.json");
        unset($response);

        $data = [
            'username' => 'test-' . $this->generateRandomString(6),
            'password' => 'test-' . $this->generateRandomString(10),
            'active' => 0,
            'is_superuser' => 0,
        ];

        $response = $this->sendAuthPost('/api/users/add.json', $data, ['token' => $token]);
        $this->assertTrue($response->isOk(), "Couldn't add user [{$data['username']}]");

        $userCreated = json_decode($response->body(), true);
        unset($response);

        $response = $this->sendAuthPost(
            '/api/users/view/' . $userCreated['data']['id'] . '.json',
            [],
            ['token' => $token]
        );
        $this->assertTrue($response->isOk(), "Couldn't view created user [{$userCreated['data']['id']}]");

        $userView = json_decode($response->body(), true);

        $this->assertEquals($userCreated['data']['id'], $userView['data']['id']);
        unset($response);

        $response = $this->sendAuthPost(
            '/api/users/edit/' . $userView['data']['id'] . '.json',
            ['password' => 'test-' . $this->generateRandomString(4)],
            ['token' => $token]
        );

        $this->assertTrue($response->isOk(), "Couldn't edit user ID [{$userView['data']['id']}]");
        unset($response);

        $response = $this->sendAuthPost('/api/users/delete/' . $userView['data']['id'] . '.json', [], ['token' => $token]);

        $this->assertTrue($response->isOk(), "Couldn't delete user ID [{$userView['data']['id']}]");
        unset($response);

        $response = $this->sendAuthPost('/api/users/view/' . $userView['data']['id'] . '.json', [], ['token' => $token]);
        $this->assertFalse($response->isOk(), "Couldn't view user ID [{$userView['data']['id']}]");
    }

    /**
     * @param string $url URL to send request to
     * @param mixed[] $data Data to send
     * @param mixed[] $headers Headers to set
     *
     * @return \Cake\Http\Client\Response
     */
    protected function sendAuthPost(string $url = '', array $data = [], array $headers = []): Response
    {
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $headers['token'],
            ],
            'type' => 'json',
        ];

        $response = $this->apiClient->post($url, $data, $options);

        return $response;
    }

    protected function generateRandomString(int $length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
