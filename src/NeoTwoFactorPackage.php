<?php

declare(strict_types=1);

namespace Vendor\NeoPHP\TwoFactorPackage;

use Neo\Core\Package\Abstract\AbstractPackage;

final class NeoTwoFactorPackage extends AbstractPackage
{
    public function getName(): string
    {
        return 'TwoFactor';
    }

    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}