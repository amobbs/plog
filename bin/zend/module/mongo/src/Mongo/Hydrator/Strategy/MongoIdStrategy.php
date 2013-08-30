<?php
/**
 * Mongo Id strategy
 * Class Rot13Strategy
 */

namespace Mongo\Hydrator\Strategy;

use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

class MongoIdStrategy implements StrategyInterface
{
    public function extract($value)
    {
        return (string) $value;
    }

    public function hydrate($value)
    {
        return ($value instanceof \MongoId) ? $value : new MongoId($value);
    }
}