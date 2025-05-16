<?php
namespace Aero\Core;

use ReflectionClass;
use Exception;

class Container
{
    protected $bindings = [];
    protected $instances = [];

    public function bind($abstract, $concrete)
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton($abstract, $concrete)
    {
        $this->bindings[$abstract] = $concrete;
        $this->instances[$abstract] = null;
        return $concrete;
    }

    public function get($abstract)
    {
        if (isset($this->instances[$abstract]) && $this->instances[$abstract]) {
            return $this->instances[$abstract];
        }

        $concrete = $this->bindings[$abstract] ?? $abstract;

        if ($concrete instanceof \Closure) {
            $object = $concrete($this);
        } else {
            $object = $this->resolve($concrete);
        }

        if (array_key_exists($abstract, $this->instances)) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    public function resolve($class)
    {
        if (!class_exists($class)) {
            throw new Exception("Class {$class} does not exist.");
        }

        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return new $class;
        }

        $dependencies = $constructor->getParameters();
        $resolved = [];

        foreach ($dependencies as $dependency) {
            $type = $dependency->getType();
            if ($type && !$type->isBuiltin()) {
                $resolved[] = $this->get($type->getName());
            } else {
                throw new Exception("Cannot resolve class dependency: {$dependency->name}");
            }
        }

        return $reflection->newInstanceArgs($resolved);
    }
}
