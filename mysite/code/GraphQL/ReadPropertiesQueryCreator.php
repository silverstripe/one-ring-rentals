<?php

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use SilverStripe\GraphQL\OperationResolver;
use SilverStripe\GraphQL\QueryCreator;

class ReadPropertiesQueryCreator extends QueryCreator implements OperationResolver
{
    public function attributes()
    {
        return [
            'name' => 'readProperties'
        ];
    }

    /**
     * The readProperties schema accepts on attribute, ID, to filter on resolve.
     *
     * @return array
     */
    public function args()
    {
        return [
            'ID' => ['type' => Type::int()]
        ];
    }

    public function type()
    {
        return Type::listOf($this->manager->getType('Property'));
    }

    public function resolve($object, array $args, $context, ResolveInfo $info)
    {
        $property = Property::singleton();
        if (!$property->canView($context['currentUser'])) {
            throw new \InvalidArgumentException(sprintf(
                '%s view access not permitted',
                Property::class
            ));
        }
        $list = Property::get();

        // Optional filtering by properties
        if (isset($args['ID'])) {
            $list = $list->filter('ID:ExactMatch', (int) $args['ID']);
        }

        return $list;
    }
}
