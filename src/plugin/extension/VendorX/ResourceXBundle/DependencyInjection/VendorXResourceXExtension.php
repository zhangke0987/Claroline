<?php

namespace VendorX\ResourceXBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

class VendorXResourceXExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $locator = new FileLocator(__DIR__ . '/../Resources/config/services');
        $loader = new YamlFileLoader($container, $locator);
        $loader->load('services.yml');
        
        $taggedServices = $container->findTaggedServiceIds("resource.manager");
        
        $serviceArray = $container->getParameter('resource.service.list');
        
        $names = array_keys($taggedServices);
        
        foreach($names as $name)
        foreach($names as $name)
        {
            $serviceArray[$name]='';
        }
        
        /*
        $container->setParameter("resource.service.list", $serviceArray);
        
        $em = $container->get('doctrine.orm.entity_manager');
        $resourcesType = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        
        var_dump('hello world');
        
        foreach($resourcesType as $resourceType)
        {
            var_dump($resourceType->getType());
        }*/
    }
}