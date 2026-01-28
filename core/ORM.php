<?php

class ORM
{
    /**
     * Mapuje tablicę asocjacyjną na obiekt danej klasy
     */
    public static function map(array $data, string $className): object
    {
        $reflection = new ReflectionClass($className);
        $constructor = $reflection->getConstructor();
        
        if ($constructor === null) {
            $obj = new $className();
            foreach ($data as $key => $value) {
                if (property_exists($obj, $key)) {
                    $obj->$key = $value;
                }
            }
            return $obj;
        }

        $params = $constructor->getParameters();
        $args = [];

        foreach ($params as $param) {
            $name = $param->getName();
            if (array_key_exists($name, $data)) {
                $args[] = $data[$name];
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                $args[] = null;
            }
        }

        return $reflection->newInstanceArgs($args);
    }

    /**
     * Konwertuje obiekt na tablicę asocjacyjną
     */
    public static function toArray(object $obj): array
    {
        return get_object_vars($obj);
    }
}
