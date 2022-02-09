<?php declare( strict_types=1 );

namespace App\ApiOperation;

use ApiPlatform\Core\Operation\PathSegmentNameGeneratorInterface;


class ApiPatchSegmentNameGenerator implements PathSegmentNameGeneratorInterface
{
    /**
     * Transforms a given string to a valid path name which can be pluralized (eg. for collections).
     *
     * @param string $name usually a ResourceMetadata shortname
     *
     * @return string A string that is a part of the route name
     */
    public function getSegmentName(string $name, bool $collection = true): string
    {
        return lcfirst(( camel_to_snake($name) ));
    }

}