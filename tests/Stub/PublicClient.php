<?php

namespace OAuth2\Test\Stub;

use OAuth2\Client\PublicClientInterface;

class PublicClient implements PublicClientInterface
{
    /**
     * @var string
     */
    private $public_id;

    /**
     * @var string[]
     */
    private $grant_types = array();

    /**
     * @var string[]
     */
    private $redirect_uris = array();

    /**
     * {@inheritdoc}
     */
    public function getPublicId()
    {
        return $this->public_id;
    }

    /**
     * @param string $public_id
     *
     * @return self
     */
    public function setPublicId($public_id)
    {
        $this->public_id = $public_id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'public_client';
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowedGrantType($grant_type)
    {
        return in_array($grant_type, $this->grant_types);
    }

    /**
     * @return string[]
     */
    public function getAllowedGrantTypes()
    {
        return $this->grant_types;
    }

    /**
     * @param string[] $grant_types
     *
     * @return self
     */
    public function setAllowedGrantTypes(array $grant_types)
    {
        $this->grant_types = $grant_types;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectUris()
    {
        return $this->redirect_uris;
    }

    /**
     * @param string[] $redirect_uris
     *
     * @return self
     */
    public function setRedirectUris(array $redirect_uris)
    {
        $this->redirect_uris = $redirect_uris;

        return $this;
    }
}
