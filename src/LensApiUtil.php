<?php

namespace Lens\Bundle\LensApiBundle;

class LensApiUtil {
    /**
     * Returns the first element of an array that matches the given
     * predicate. If no element matches, returns null.
     *
     * @param callable $predicate
     * @param iterable $haystack
     * @param bool     $returnKey If true the key is returned instead of the value.
     *
     * @return mixed
     */
    public static function ArrayFind(callable $predicate, iterable $haystack, bool $returnKey = false): mixed
    {
        foreach ($haystack as $key => $value) {
            if (true === $predicate($value, $key)) {
                return true === $returnKey
                    ? $key
                    : $value;
            }
        }

        return null;
    }

    /**
     * Returns true if at least one of the elements in the array passes the predicate.
     */
    public static function ArrayAny(callable $predicate, iterable $haystack): bool
    {
        foreach ($haystack as $key => $value) {
            if (true === $predicate($value, $key)) {
                return true;
            }
        }

        return false;
    }
}
