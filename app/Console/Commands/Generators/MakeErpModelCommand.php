<?php

namespace App\Console\Commands\Generators;

use Illuminate\Console\GeneratorCommand;

class MakeErpModelCommand extends GeneratorCommand
{
    protected $name = 'erp:make:model';

    protected $description = 'Creates new model class with necessary default traits and class fields';

    protected $type = 'Model';

    /**
     * {@inheritDoc}
     */
    protected function getStub()
    {
        return __DIR__.DIRECTORY_SEPARATOR.'stubs'.DIRECTORY_SEPARATOR.'erp-model.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Models';
    }

    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        return $stub;
    }
}
