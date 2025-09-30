<?php

namespace Aslnbxrz\MyID;

use SocialiteProviders\Manager\OAuth2\User as OAuth2User;

class MyIDUser extends OAuth2User
{
    public function getPhone(): ?string
    {
        return $this->attributes['phone'] ?? null;
    }

    // common_data helpers
    public function getPinfl(): ?string
    {
        return $this->attributes['pinfl'] ?? null;
    }

    public function getBirthDate(): ?string
    {
        return $this->attributes['birth_date'] ?? null;
    }
}


