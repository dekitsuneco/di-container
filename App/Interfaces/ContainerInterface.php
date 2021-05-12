<?php

namespace App\Interfaces;

interface ContainerInterface
{
    public function get(string $className): object;
    public function has(string $className): bool;
}
