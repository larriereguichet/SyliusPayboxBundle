<?php

namespace Librinfo\SyliusPayboxBundle;

use Librinfo\SyliusPayboxBundle\DependencyInjection\LibrinfoSyliusPayboxExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class LibrinfoSyliusPayboxBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new LibrinfoSyliusPayboxExtension();
        }

        return $this->extension;
    }
}
