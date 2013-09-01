<?php
/**
 * Mongo Id strategy
 * Rewrites the ID to and from MongoID instances
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