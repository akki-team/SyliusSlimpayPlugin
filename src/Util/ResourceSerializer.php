<?php

namespace Akki\SyliusSlimpayPlugin\Util;

use HapiClient\Hal\Link;
use HapiClient\Hal\Resource;

class ResourceSerializer
{
    /**
     * @param Resource $resource
     *
     * @return string
     */
    public static function serializeResource(Resource $resource)
    {
        $state = $resource->getState();
        $links = $resource->getAllLinks();
        $linkArrays = [];
        foreach ($links as $key => $link)
        {
            $linkArrays[$key] = self::linkToArray($link);
        }
        $embeddedResources = $resource->getAllEmbeddedResources();

        $state = array_merge($state, [
            '_links' => $linkArrays,
            '_embedded' => $embeddedResources,

        ]);

        return json_encode($state);
    }

    private static function linkToArray(Link $link)
    {
        return [
            'href' => $link->getHref(),
            'templated'=> $link->isTemplated(),
            'type'=> $link->getType(),
            'deprecation'=> $link->getDeprecation(),
            'name'=> $link->getName(),
            'profile'=> $link->getProfile(),
            'title'=> $link->getTitle(),
            'hreflang'=> $link->getHreflang(),
        ];
    }

    /**
     * @param string $json
     *
     * @return Resource
     */
    public static function unserializeResource($json)
    {
        return Resource::fromJson($json);
    }
}