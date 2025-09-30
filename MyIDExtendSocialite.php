<?php

namespace Aslnbxrz\MyID;

use SocialiteProviders\Manager\SocialiteWasCalled;

class MyIDExtendSocialite
{
    public function handle(SocialiteWasCalled $event): void
    {
        $event->extendSocialite('myid', Provider::class);
    }
}


