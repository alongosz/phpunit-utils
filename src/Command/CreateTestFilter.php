<?php
/**
 * Copyright (c) 2017, Andrew Longosz
 */

namespace Awl\PHPUnitUtils\Command;

use PHPUnit\Util\Test as TestUtil;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateTestFilter extends Command
{
    public function configure()
    {
        parent::configure();

        $this
            ->setName('create-filter')
            ->setDescription('Create shortest possible dependency filter to run unit test')
            ->addOption(
                'autoloader-path',
                'a',
                InputOption::VALUE_OPTIONAL,
                'External autoloader path',
                getcwd() . '/vendor/autoload.php'
            )
            ->addOption('show-fqcn', 'f', InputOption::VALUE_NONE, 'Show filter value including FQCN')
            ->addArgument('method-reference', InputArgument::REQUIRED, 'Format: \'FQCN::methodName\'');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $methodReference = $input->getArgument('method-reference');
        $showFQCN = $input->getOption('show-fqcn');

        // Use external autoloader, hopefully the one from current project
        $this->loadExternalAutoloader($input->getOption('autoloader-path'));

        $dependencies = [];
        $dependencies = $this->findAllDependencies($methodReference, $dependencies);
        $filterOutput = '';
        foreach ($dependencies as $dependency) {
            if (!empty($filterOutput)) {
                $filterOutput .= '|';
            }
            $name = $showFQCN ? addslashes($dependency) : explode('::', $dependency)[1];
            $filterOutput .= $name;
        }

        $output->writeln("'$filterOutput'");
    }

    /**
     * Load external autoloader.
     *
     * @todo Is there more sane way to do it?
     */
    private function loadExternalAutoloader($autoloaderPath)
    {
        // @@ BEGIN HACK
        if (!is_readable($autoloaderPath)) {
            throw new InvalidArgumentException(
                "Invalid autoloader path. File does not exist or is not readable: {$autoloaderPath}"
            );
        }

        require_once $autoloaderPath;
        // @@ END HACK
    }

    /**
     * Recursively find all dependencies.
     *
     * @param string $methodReference FQCN::methodName
     * @param string[] $dependencies already found dependencies
     *
     * @return string[] List of Test cascade dependencies in the format of FQCN::methodName
     */
    private function findAllDependencies($methodReference, array $dependencies)
    {
        list($className, $methodName) = explode('::', $methodReference, 2);
        foreach (TestUtil::getDependencies($className, $methodName) as $dependency) {
            $dependencies = array_merge($dependencies, $this->findAllDependencies($dependency, $dependencies));
            $dependencies[] = $dependency;
        }

        return array_unique($dependencies);
    }
}
