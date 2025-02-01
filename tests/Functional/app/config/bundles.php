<?php

declare(strict_types=1);

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use SwooleBundle\ResetterBundle\SwooleBundleResetterBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

return [
    new FrameworkBundle(),
    new DoctrineBundle(),
    new SwooleBundleResetterBundle(),
];
