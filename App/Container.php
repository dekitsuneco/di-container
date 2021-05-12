<?php

namespace App;

use App\Interfaces\ContainerInterface;
use Exception;
use ReflectionClass;

class Container implements ContainerInterface
{
    protected $instances = [];
    // Add an instruction of the particular object creation to the container:
    public function set(string $className, object $value): void
    {
        $this->instances[$className] = $value;
    }

    public function get(string $className): object
    {
        // If object already exists in the container, simply return it:
        if ($this->has($className)) {
            return $this->instances[$className];
        }
        // If it doesn't, create it, add it to the container and then return it:
        $instance = $this->createObject($className);
        $this->instances[$className] = $instance;

        return $instance;
    }

    public function has(string $className): bool
    {
        return isset($this->instances[$className]);
    }

    protected function createObject(string $className): object
    {
        // Throw an exception if this class doesn't exist:
        if (!class_exists($className)) {
            throw new Exception("Class {$className} doesn't exist");
        }
        // Create a reflection class which has info about given class:
        $reflectionClass = new ReflectionClass($className);
        // If a given class has a default constructor, then
        // create an instance of the class and return it:
        if ($reflectionClass->getConstructor() == null) {
            return new $className();
        }
        // Get list of parameters of constructor:
        $parameters = $reflectionClass->getConstructor()->getParameters();
        // Get an array of objects which are needed
        // as the parameters for the constructor:
        $dependencies = $this->buildDependencies($parameters);

        return $reflectionClass->newInstanceArgs($dependencies);
    }

    protected function buildDependencies(array $parameters): array
    {
        // Get a list of needed objects:
        $dependencies = [];

        $classNames = array_map(function ($parameter) {
            $type = $parameter->getType();
            return isset($type) ? $type->getName() : null;
        }, $parameters);

        $dependencies = array_map(fn ($className) => $this->createObject($className), $classNames);

        return $dependencies;
    }
}
