<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services\Github;

use Github\Client;

class AuthenticatedClientDecorator extends Client
{
    /**
     * @var string
     */
    private $token;

    public function __call($name, $args)
    {
        parent::authenticate($this->token, Client::AUTH_HTTP_TOKEN);

        return parent::__call($name, $args);
    }

    public function authenticate($tokenOrLogin, $password = null, $authMethod = null)
    {
        $this->token = $tokenOrLogin;

        parent::authenticate($tokenOrLogin, $password, $authMethod);
    }
}
