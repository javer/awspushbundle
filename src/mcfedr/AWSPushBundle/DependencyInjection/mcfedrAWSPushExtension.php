<?php

namespace mcfedr\AWSPushBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class mcfedrAWSPushExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('mcfedr_aws_push.platforms', $config['platforms']);
        $container->setParameter('mcfedr_aws_push.aws.key', $config['aws']['key']);
        $container->setParameter('mcfedr_aws_push.aws.secret', $config['aws']['secret']);
        $container->setParameter('mcfedr_aws_push.aws.region', $config['aws']['region']);
        $container->setParameter('mcfedr_aws_push.debug', $config['debug']);

        if (isset($config['topic_name'])) {
            $container->setParameter('mcfedr_aws_push.topic_name', $config['topic_name']);
        }

        if (isset($config['cache'])) {
            $cacheName = $config['cache'];
        } else {
            $cacheName = 'mcfedr_aws_push.cache';
            $container->setDefinition(
                $cacheName,
                new Definition(
                    'Doctrine\Common\Cache\FilesystemCache', [
                        "%kernel.cache_dir%/mcfedr_aws_push"
                    ]
                )
            );
        }

        $container->setDefinition(
            'mcfedr_aws_push.topics',
            new Definition(
                'mcfedr\AWSPushBundle\Service\Topics', [
                    new Reference('mcfedr_aws_push.sns_client'),
                    new Reference('logger'),
                    new Reference('mcfedr_aws_push.messages'),
                    new Reference($cacheName),
                    $container->getParameter('mcfedr_aws_push.debug')
                ]
            )
        );
    }
}
