<?php
namespace CCV\Configuration;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Definition\Processor;

class YamlConfigLoader extends FileLoader
{
    public function load($resource, $type = null) {
        $config = Yaml::parse($resource);
        $processor = new Processor();
        $configuration = new Configuration();
        return $processor->processConfiguration(
            $configuration,
            array($config)
        );
    }

    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'yml' === pathinfo(
            $resource,
            PATHINFO_EXTENSION
        );
    }
}