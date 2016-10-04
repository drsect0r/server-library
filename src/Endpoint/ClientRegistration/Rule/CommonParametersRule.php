<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\Endpoint\ClientRegistration\Rule;

use Assert\Assertion;

final class CommonParametersRule extends AbstractInternationalizedRule
{
    /**
     * {@inheritdoc}
     */
    public function checkParameters(array $registration_parameters, array &$metadatas, array $previous_metadata = [])
    {
        foreach ($this->getSupportedParameters() as $parameter => $closure) {
            $metadatas = array_merge(
                $metadatas,
                $this->getInternationalizedParameters($registration_parameters, $parameter, $closure)
            );
        }
    }

    /**
     * @return array
     */
    private function getSupportedParameters()
    {
        return [
            'client_name' => function($k, $v) {},
            'client_uri' => $this->getUriVerificationClosure(),
            'logo_uri' => $this->getUriVerificationClosure(),
            'tos_uri' => $this->getUriVerificationClosure(),
            'policy_uri' => $this->getUriVerificationClosure(),
        ];
    }

    private function getUriVerificationClosure()
    {
        return function ($k, $v) {
            Assertion::url($v, sprintf('The parameter with key "%s" is not a valid URL.', $k));
        };
    }
}
