<?php

namespace APinnecke\Bundle\OAuthBundle\ServiceFactory;

use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Exception\Exception;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\ServiceFactory as BaseServiceFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ServiceFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var BaseServiceFactory
     */
    private $factory;

    /**
     * @var TokenStorageInterface
     */
    private $storage;

    /**
     * @var array
     */
    private $serviceCache = array();

    /**
     * @param ContainerInterface $container
     * @param BaseServiceFactory $factory
     * @param TokenStorageInterface $storage
     */
    public function __construct(ContainerInterface $container, BaseServiceFactory $factory, TokenStorageInterface $storage)
    {
        $this->container = $container;
        $this->factory = $factory;
        $this->storage = $storage;
    }

    /**
     * @param string $name
     * @param string $client
     * @param string $secret
     * @param array $scopes
     * @param array|string $url
     * @return \OAuth\Common\Service\ServiceInterface
     * @throws Exception
     */
    public function createService($name, $client, $secret, $url = null, array $scopes = [])
    {
        if (!isset(ResourceOwners::$all[$name])) {
            throw new Exception('Resource owner ' . $name . ' is not available');
        }

        if (isset($this->serviceCache[$name])) {
            return $this->serviceCache[$name];
        }

        if (is_array($url)) {
            $url = $this->container->get('router')->generate($url[0], $url[1], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $credentials = new Credentials($client, $secret, $url);

        return $this->serviceCache[$name] = $this->factory->createService($name, $credentials, $this->storage, $scopes);
    }
}
