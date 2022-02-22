<?php
/**
 * Created by PhpStorm.
 * User: leandro
 * Date: 15/09/14
 * Time: 15:22
 */

namespace Thundera\Util;

use Doctrine\ODM\MongoDB\PersistentCollection;
use Doctrine\Common\Util\Debug;

class Parse {

    public static function toArray($object)
    {
        $reflectionClass = new \ReflectionClass(get_class($object));
        $array = array();
        foreach ($reflectionClass->getProperties() as $property) {
            $property->setAccessible(true);
            $propertyValue = $property->getValue($object);
            $propertyName = $property->getName();
            if($propertyValue instanceof PersistentCollection) {
                $array[$propertyName] = $propertyValue->toArray();
                foreach($propertyValue->toArray() as $key => $value) {
                    $array[$propertyName][$key] = self::toArray($value);
                }
                $array[$propertyName] = array_values($array[$propertyName]);
                continue;
            }
            $array[$propertyName] = (is_object($propertyValue)) ? self::toArray($propertyValue) : $propertyValue;
            $property->setAccessible(false);
        }
        return $array;
    }

    public static function objToJson($data)
    {
        return array("data"=>json_encode(self::arrayRecursive(self::toArray($data))));
    }

    public static function diffToJson($before, $after)
    {
        return array('before' => json_encode(self::arrayRecursiveDiff($after, $before)), 'after' => json_encode(self::arrayRecursiveDiff($before, $after)));
    }

    public static function arrayRecursiveDiff($before, $after)
    {
        $diff = array();

        foreach ($after as $key => $value) {
            if (is_array($before) && array_key_exists($key, $before)) {
                if (is_array($value)) {
                    $recursiveDiff = self::arrayRecursiveDiff($before[$key], $value);
                    if (count($recursiveDiff)) {
                        $diff[$key] = $recursiveDiff;
                    }
                } elseif ($value != $before[$key]) {
                    $diff[$key] = $value;
                }
                continue;
            }
            $diff[$key] = $value;
        }
        return $diff;
    }

    public static function arrayRecursive($data)
    {
        $recursive = array();

        foreach ($data as $key => $value) {

            if (is_array($value)) {
                $recursiveDiff = self::arrayRecursive($value);
                if (count($recursiveDiff)) {
                    $recursive[$key] = $recursiveDiff;
                }
                continue;
            }

            $recursive[$key] = $value;
        }

        return $recursive;
    }

}