<?php

namespace OAuth2\Grant;

use OAuth2\Behaviour\HasAccessTokenManager;
use OAuth2\Behaviour\HasAccessTokenTypeManager;
use OAuth2\Endpoint\Authorization;

class ImplicitGrantType implements ResponseTypeSupportInterface
{
    use HasAccessTokenTypeManager;
    use HasAccessTokenManager;

    /**
     * {@inheritdoc}
     */
    public function getResponseType()
    {
        return 'token';
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseMode()
    {
        return 'fragment';
    }

    /**
     * {@inheritdoc}
     */
    public function grantAuthorization(Authorization $authorization)
    {
        $token = $this->getAccessTokenManager()->createAccessToken($authorization->getClient(), $authorization->getEndUser(), $authorization->getScope());
        $params = $this->getAccessTokenTypeManager()->getDefaultAccessTokenType()->prepareAccessToken($token);

        $state = $authorization->getState();
        if (!empty($state)) {
            $params['state'] = $state;
        }

        return $params;
    }
}