<?php

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestCase;
use Cake\Utility\Security;
use Firebase\JWT\JWT;

class BaseIntegrationTestCase extends IntegrationTestCase
{
    /**
     * Set API Headers for Integration Case Request object
     *
     * @param mixed[] $arguments that might contain user specific data
     *
     * @return void
     */
    protected function setApiHeaders(array $arguments = []): void
    {
        $options = [];

        if (!empty($arguments['user_id'])) {
            $token = JWT::encode(
                ['sub' => $arguments['user_id'], 'exp' => time() + 604800],
                Security::getSalt()
            );

            $options['token'] = $token;
        }

        $data = $this->_setConfigRequest($options);

        $this->configRequest($data);
    }

    /**
     * Set configRequest integration method call
     *
     * Prepopulates Requests Header with extra info like token
     *
     * @param mixed[] $params with request configs
     *
     * @return mixed[] $data of the request headers/params.
     */
    private function _setConfigRequest(array $params = []): array
    {
        $data = [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];

        if (!empty($params['token'])) {
            $data['headers']['authorization'] = 'Bearer ' . $params['token'];
        }

        return $data;
    }
}
