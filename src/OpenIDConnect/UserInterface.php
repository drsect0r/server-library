<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\OpenIDConnect;

use OAuth2\User\UserInterface as BaseUserInterface;

/**
 * This interface must be implemented by end-user classes.
 */
interface UserInterface extends BaseUserInterface
{
    /**
     * @param string[] $scopes
     *
     * @return array
     */
    public function getUserInfo(array $scopes);

    /**
     * @return null|int
     */
    public function getLastLoginAt();

    /**
     * @return string|null
     */
    public function getDisplayName();

    /**
     * @return string|null
     */
    public function getGivenName();

    /**
     * @return string|null
     */
    public function getMiddleName();

    /**
     * @return string|null
     */
    public function getFamilyName();

    /**
     * @return string|null
     */
    public function getNickname();

    /**
     * @return string|null
     */
    public function getPreferredUsername();

    /**
     * @return string|null
     */
    public function getProfile();

    /**
     * @return string|null
     */
    public function getPicture();

    /**
     * @return string|null
     */
    public function getWebsite();

    /**
     * @return string|null
     */
    public function getGender();

    /**
     * @return string|null
     */
    public function getBirthdate();

    /**
     * @return string|null
     */
    public function getZoneInfo();

    /**
     * @return string|null
     */
    public function getLocale();

    /**
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * @return string|null
     */
    public function getEmail();

    /**
     * @return bool|null
     */
    public function isEmailVerified();

    /**
     * @return string|null
     */
    public function getPhoneNumber();

    /**
     * @return bool|null
     */
    public function isPhoneNumberVerified();

    /**
     * @return \OAuth2\OpenIdConnect\AddressInterface|null
     */
    public function getAddress();

    /**
     * @return string[]
     */
    public function getAuthenticationMethodsReferences();

    /**
     * @return string[]
     */
    public function getAuthenticationContextClassReference();
}
