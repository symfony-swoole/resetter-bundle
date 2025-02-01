<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Functional\app;

use Exception;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

final class AppKernel extends Kernel
{
    private readonly string $rootConfig;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        private readonly string $varDir,
        private readonly string $testCase,
        string $rootConfig,
        string $environment,
        bool $debug,
    ) {
        if (!is_dir(__DIR__ . '/' . $testCase)) {
            throw new InvalidArgumentException(sprintf('The test case "%s" does not exist.', $testCase));
        }

        $filesystem = new Filesystem();
        $rootConfig = __DIR__ . '/' . $testCase . '/' . $rootConfig;

        if (!$filesystem->isAbsolutePath($rootConfig) && !is_file($rootConfig)) {
            throw new InvalidArgumentException(sprintf('The root config "%s" does not exist.', $rootConfig));
        }

        $this->rootConfig = $rootConfig;

        parent::__construct($environment, $debug);
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    /**
     * @return iterable<BundleInterface>
     * @throws RuntimeException
     */
    public function registerBundles(): iterable
    {
        $filename = $this->getRootDir() . '/config/bundles.php';

        if (!is_file($filename)) {
            throw new RuntimeException(sprintf('The bundles file "%s" does not exist.', $filename));
        }

        return include $filename;
    }

    public function getRootDir(): string
    {
        return __DIR__;
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/' . $this->varDir . '/' . $this->testCase . '/cache/' . $this->environment;
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/' . $this->varDir . '/' . $this->testCase . '/logs';
    }

    /**
     * @throws Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load($this->rootConfig);
    }

    public function serialize(): string
    {
        return serialize([
            $this->varDir,
            $this->testCase,
            $this->rootConfig,
            $this->getEnvironment(),
            $this->isDebug(),
        ]);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function unserialize(string $str): void
    {
        $data = unserialize($str);
        $this->__construct($data[0], $data[1], $data[2], $data[3], $data[4]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getKernelParameters(): array
    {
        $parameters = parent::getKernelParameters();
        $parameters['kernel.test_case'] = $this->testCase;

        return $parameters;
    }
}
