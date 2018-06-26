<?php

class ResourceDeleteAlert_Proxy
{   
    public static function extendPublicResourceController($class, &$extend)
    {
        $extend[] = 'ResourceDeleteAlert_Extends_XenResource_ControllerPublic_Resource';
    }
}