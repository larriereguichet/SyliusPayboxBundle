<?php

/*
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU GPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Sil\Bundle\SyliusPayboxBundle;

use Sil\Bundle\SyliusPayboxBundle\DependencyInjection\SilSyliusPayboxExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SilSyliusPayboxBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new SilSyliusPayboxExtension();
        }

        return $this->extension;
    }
}
