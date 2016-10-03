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

abstract class AbstractInternationalizedRule implements ClientRegistrationRuleInterface
{
    /**
     * @param array    $request_parameters
     * @param string   $base
     * @param \closure $closure
     *
     * @return array
     */
    protected function getInternationalizedParameters(array $request_parameters, $base, $closure)
    {
        $result = [];
        foreach ($request_parameters as $k=>$v) {
            if ($base === $k ) {
                $closure($k, $v);
                $result[$k] = $v;

                continue;
            }

            $sub = mb_substr($k, 0, mb_strlen($base, '8bit') + 1, '8bit');
            if (sprintf('%s#', $base) === $sub && !empty(mb_substr($k, mb_strlen($base, '8bit') + 1, null, '8bit'))) {
                $closure($k, $v);
                $result[$k] = $v;

                continue;
            }
        }

        return $result;
    }
}
