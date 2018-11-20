<?php
namespace App\Controller\Api\V1\V0;

use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Utility\Security;
use Firebase\JWT\JWT;

/**
    @SWG\Post(
        path="/api/users/token",
        summary="API authentication",
        tags={"Users Authentication"},
        consumes={"application/json"},
        produces={"application/json"},
        @SWG\Parameter(
            name="body",
            in="body",
            description="API credentials",
            required=true,
            @SWG\Schema(ref="#/definitions/AuthToken")
        ),
        @SWG\Response(
            response="200",
            description="Successful operation"
        )
    )

    @SWG\Definition(
        definition="AuthToken",
        required={"username,password"},
        @SWG\Property(
            property="username",
            type="string"
        ),
        @SWG\Property(
            property="password",
            type="string"
        )
    )
 */
class UsersController extends AppController
{
    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        // allow public access to token action
        try {
            parent::initialize();
        } catch (ForbiddenException $e) {
            if ('token' !== $this->request->action) {
                throw new ForbiddenException($e->getMessage());
            }
        }

        if (Configure::read('API.auth')) {
            $this->Auth->allow(['token']);
        }
    }

    /**
     * Method responsible for generating JSON Web Token (WT), after it authenticates the user.
     *
     * @throws \Cake\Http\Exception\UnauthorizedException
     * @link   http://www.bravo-kernel.com/2015/04/how-to-add-jwt-authentication-to-a-cakephp-3-rest-api/
     * @return \Cake\Http\Response|void|null
     */
    public function token()
    {
        $user = $this->Auth->identify();
        if (!$user) {
            throw new UnauthorizedException('Invalid username or password');
        }

        $this->set([
            'success' => true,
            'data' => [
                'token' => JWT::encode(
                    [
                        'sub' => $user['id'],
                        'exp' => time() + 604800
                    ],
                    Security::salt()
                )
            ],
            '_serialize' => ['success', 'data']
        ]);
    }
}
