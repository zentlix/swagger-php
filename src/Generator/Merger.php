<?php

declare(strict_types=1);

namespace Spiral\OpenApi\Generator;

use OpenApi\Annotations\AbstractAnnotation;
use OpenApi\Context;
use OpenApi\Generator;

final class Merger
{
    public static function merge(
        AbstractAnnotation $annotation,
        array|AbstractAnnotation|\ArrayObject $from,
        bool $overwrite = false
    ): void {
        switch (true) {
            case \is_array($from):
                self::mergeFromArray($annotation, $from, $overwrite);
                break;
            case $from instanceof AbstractAnnotation:
                self::mergeFromArray($annotation, json_decode(json_encode($from), true), $overwrite);
                break;
            case $from instanceof \ArrayObject:
                self::mergeFromArray($annotation, $from->getArrayCopy(), $overwrite);
        }
    }

    private static function mergeFromArray(AbstractAnnotation $annotation, array $properties, bool $overwrite): void
    {
        $done = [];
        $defaults = get_class_vars(\get_class($annotation));

        foreach ($annotation::$_nested as $className => $propertyName) {
            if (\is_string($propertyName)) {
                if (\array_key_exists($propertyName, $properties)) {
                    if (!\is_bool($properties[$propertyName])) {
                        self::mergeChild($annotation, $className, $properties[$propertyName], $overwrite);
                    } elseif ($overwrite || $annotation->{$propertyName} === $defaults[$propertyName]) {
                        // Support for boolean values (for instance for additionalProperties)
                        $annotation->{$propertyName} = $properties[$propertyName];
                    }
                    $done[] = $propertyName;
                }
            } elseif (\array_key_exists($propertyName[0], $properties)) {
                $collection = $propertyName[0];
                $property = $propertyName[1] ?? null;
                self::mergeCollection($annotation, $className, $property, $properties[$collection], $overwrite);
                $done[] = $collection;
            }
        }

        foreach ($annotation::$_types as $propertyName => $type) {
            if (\array_key_exists($propertyName, $properties)) {
                self::mergeTyped($annotation, $propertyName, $type, $properties, $defaults, $overwrite);
                $done[] = $propertyName;
            }
        }

        foreach ($properties as $propertyName => $value) {
            if ('$ref' === $propertyName) {
                $propertyName = 'ref';
            }
            if (!\in_array($propertyName, $done, true)) {
                self::mergeProperty($annotation, $propertyName, $value, $defaults[$propertyName], $overwrite);
            }
        }
    }

    private static function mergeChild(
        AbstractAnnotation $annotation,
        mixed $className,
        mixed $value,
        bool $overwrite
    ): void {
        self::merge(self::getChild($annotation, $className), $value, $overwrite);
    }

    private static function mergeCollection(
        AbstractAnnotation $annotation,
        mixed $className,
        mixed $property,
        iterable $items,
        bool $overwrite
    ): void {
        if (null !== $property) {
            foreach ($items as $prop => $value) {
                $child = self::getIndexedCollectionItem($annotation, $className, (string) $prop);
                self::merge($child, $value);
            }
        } else {
            $nesting = self::getNestingIndexes($className);
            foreach ($items as $props) {
                $create = [];
                $merge = [];
                foreach ($props as $k => $v) {
                    if (\in_array($k, $nesting, true)) {
                        $merge[$k] = $v;
                    } else {
                        $create[$k] = $v;
                    }
                }
                self::merge(self::getCollectionItem($annotation, $className, $create), $merge, $overwrite);
            }
        }
    }

    private static function mergeTyped(
        AbstractAnnotation $annotation,
        string $propertyName,
        mixed $type,
        array $properties,
        array $defaults,
        bool $overwrite
    ): void {
        if (\is_string($type) && str_starts_with($type, '[')) {
            $innerType = substr($type, 1, -1);

            if (!$annotation->{$propertyName} || Generator::UNDEFINED === $annotation->{$propertyName}) {
                $annotation->{$propertyName} = [];
            }

            if (!class_exists($innerType)) {
                /* type is declared as array in @see AbstractAnnotation::$_types */
                $annotation->{$propertyName} = array_unique(array_merge(
                    (array) $annotation->{$propertyName},
                    $properties[$propertyName]
                ));

                return;
            }

            // $type == [Schema] for instance
            foreach ($properties[$propertyName] as $child) {
                $annotation->{$propertyName}[] = $annot = self::createChild($annotation, $innerType, []);
                self::merge($annot, $child, $overwrite);
            }
        } else {
            self::mergeProperty(
                $annotation,
                $propertyName,
                $properties[$propertyName],
                $defaults[$propertyName],
                $overwrite
            );
        }
    }

    private static function mergeProperty(
        AbstractAnnotation $annotation,
        string $propertyName,
        mixed $value,
        mixed $default,
        bool $overwrite
    ): void {
        if (true === $overwrite || $default === $annotation->{$propertyName}) {
            $annotation->{$propertyName} = $value;
        }
    }

    private static function getNestingIndexes(mixed $class): array
    {
        return array_values(
            array_map(static fn (mixed $value): mixed => \is_array($value) ? $value[0] : $value, $class::$_nested)
        );
    }

    private static function getChild(
        AbstractAnnotation $parent,
        mixed $class,
        array $properties = []
    ): AbstractAnnotation {
        $nested = $parent::$_nested;
        $property = $nested[$class];

        if (null === $parent->{$property} || Generator::UNDEFINED === $parent->{$property}) {
            $parent->{$property} = self::createChild($parent, $class, $properties);
        }

        return $parent->{$property};
    }

    private static function createChild(
        AbstractAnnotation $parent,
        mixed $class,
        array $properties = []
    ): AbstractAnnotation {
        $nesting = self::getNestingIndexes($class);

        if (!empty(array_intersect(array_keys($properties), $nesting))) {
            throw new \InvalidArgumentException('Nesting Annotations is not supported.');
        }

        /** @psalm-var AbstractAnnotation $annotation */
        $annotation = new $class(
            array_merge($properties, ['_context' => new Context(['nested' => $parent], $parent->_context)])
        );

        return $annotation;
    }

    private static function getIndexedCollectionItem(
        AbstractAnnotation $parent,
        string $class,
        mixed $value
    ): AbstractAnnotation {
        $nested = $parent::$_nested;
        /**
         * @psalm-suppress PossiblyInvalidArrayAccess
         * @psalm-suppress PossiblyInvalidArrayOffset
         */
        [$collection, $property] = $nested[$class];

        $key = self::searchIndexedCollectionItem(
            $parent->{$collection} && Generator::UNDEFINED !== $parent->{$collection} ? $parent->{$collection} : [],
            $property,
            $value
        );

        if (false === $key) {
            $key = self::createCollectionItem($parent, $collection, $class, [$property => $value]);
        }

        return $parent->{$collection}[$key];
    }

    private static function searchIndexedCollectionItem(
        array $collection,
        int|null|string $member,
        mixed $value
    ): bool|int|string {
        return array_search($value, array_column($collection, $member), true);
    }

    private static function createCollectionItem(
        AbstractAnnotation $parent,
        string $collection,
        string $class,
        array $properties = []
    ): int {
        if (Generator::UNDEFINED === $parent->{$collection}) {
            $parent->{$collection} = [];
        }

        $key = \count($parent->{$collection} ?: []);
        $parent->{$collection}[$key] = self::createChild($parent, $class, $properties);

        return $key;
    }

    private static function getCollectionItem(
        AbstractAnnotation $parent,
        string $class,
        array $properties = []
    ): AbstractAnnotation {
        $key = null;
        $nested = $parent::$_nested;
        /** @psalm-suppress PossiblyInvalidArrayOffset */
        $collection = $nested[$class][0];

        if (!empty($properties)) {
            $key = self::searchCollectionItem(
                $parent->{$collection} && Generator::UNDEFINED !== $parent->{$collection} ? $parent->{$collection} : [],
                $properties
            );
        }
        if (null === $key) {
            $key = self::createCollectionItem($parent, $collection, $class, $properties);
        }

        return $parent->{$collection}[$key];
    }

    private static function searchCollectionItem(array $collection, array $properties): int|string|null
    {
        foreach ($collection ?: [] as $i => $child) {
            foreach ($properties as $k => $prop) {
                if ($child->{$k} !== $prop) {
                    continue 2;
                }
            }

            return $i;
        }

        return null;
    }
}
