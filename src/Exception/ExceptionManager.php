<?php

namespace OAuth2\Exception;

use OAuth2\Behaviour\HasConfiguration;

/**
 * An exception manager.
 */
class ExceptionManager implements ExceptionManagerInterface
{
    use HasConfiguration;

    /**
     * {@inheritdoc}
     */
    public function getUri($type, $error, $error_description = null, array $data = [])
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getException($type, $error, $error_description = null, array $data = [])
    {
        if ($type === self::AUTHENTICATE && !isset($data['realm'])) {
            $data['realm'] = $this->getConfiguration()->get('realm', 'Service');
        }

        $error_uri = $this->getUri($type, $error, $error_description, $data);

        $supported_types = [
            self::AUTHENTICATE,
            self::BAD_REQUEST,
            self::NOT_IMPLEMENTED,
            self::REDIRECT,
            self::INTERNAL_SERVER_ERROR,
        ];

        if (in_array($type, $supported_types)) {
            $exception = sprintf('OAuth2\Exception\%sException', $type);

            return new $exception($error, $error_description, $error_uri, $data);
        }

        throw new \InvalidArgumentException('Unsupported type');
    }
}