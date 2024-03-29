<?php

/*
 * This file is part of the CRUDlexSilex2 package.
 *
 * (c) Philip Lehmann-Böhm <philip@philiplb.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CRUDlex\Silex;

use CRUDlex\EntityDefinitionFactory;
use CRUDlex\EntityDefinitionValidator;
use CRUDlex\Service;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Silex\ServiceProviderInterface;
use Silex\Application;
use Silex\Provider\LocaleServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\Translation\Loader\YamlFileLoader;

/**
 * The ServiceProvider setups and initializes the service for Silex.
 */
class ServiceProvider implements ServiceProviderInterface
{

    /**
     * Holds the directory of the locales.
     * @var string
     */
    protected $localeDir;

    /**
     * Initializes the available locales.
     *
     * @param Application $app
     * the application container
     */
    protected function initLocales(Application $app)
    {
        $locales = Service::getLocales();
        $app['translator']->addLoader('yaml', new YamlFileLoader());
        foreach ($locales as $locale) {
            $app['translator']->addResource('yaml', $this->localeDir.'/'.$locale.'.yml', $locale);
        }
    }

    /**
     * Initializes needed but yet missing service providers.
     *
     * @param Application $app
     * the application container
     */
    protected function initMissingServiceProviders(Application $app)
    {

        if (!$app->offsetExists('translator')) {
            $app->register(new LocaleServiceProvider());
            $app->register(new TranslationServiceProvider(), [
                'locale_fallbacks' => ['en'],
            ]);
        }

        if (!$app->offsetExists('session')) {
            $app->register(new SessionServiceProvider());
        }

        if (!$app->offsetExists('twig')) {
            $app->register(new TwigServiceProvider());
        }
    }

    /**
     * Creates an EntityDefinitionValidator according to the configuration.
     *
     * @param Container $app
     * the Container instance of the Silex application
     */
    protected function createEntityDefinitionValidator(Application $app)
    {
        $doValidate = !$app->offsetExists('crud.validateentitydefinition') || $app['crud.validateentitydefinition'] === true;
        $validator  = null;
        if ($doValidate) {
            $validator = $app->offsetExists('crud.entitydefinitionvalidator')
                ? $app['crud.entitydefinitionvalidator']
                : new EntityDefinitionValidator();
        }
        return $validator;
    }

    /**
     * ServiceProvider constructor.
     */
    public function __construct()
    {
        $this->localeDir = __DIR__.'/../../../../crudlex/src/locales';
    }

    /**
     * Sets the directory containing the locales.
     *
     * @param string $localeDir
     * the directory containing the locales.
     */
    public function setLocaleDir($localeDir)
    {
        $this->localeDir = $localeDir;
    }

    /**
     * Implements ServiceProviderInterface::register() registering $app['crud'].
     * $app['crud'] contains an instance of the ServiceProvider afterwards.
     *
     * @param Application $app
     * the Application instance of the Silex application
     */
    public function register(Application $app)
    {
        if (!$app->offsetExists('crud.filesystem')) {
            $app['crud.filesystem'] = new Filesystem(new Local(getcwd()));
        }

        $validator = $this->createEntityDefinitionValidator($app);

        $app['crud'] = function() use ($app, $validator) {
            $crudFileCachingDirectory = $app->offsetExists('crud.filecachingdirectory') ? $app['crud.filecachingdirectory'] : null;
            $entityDefinitionFactory  = $app->offsetExists('crud.entitydefinitionfactory') ? $app['crud.entitydefinitionfactory'] : new EntityDefinitionFactory();
            $result                   = new Service($app['crud.file'], $crudFileCachingDirectory, $app['url_generator'], $app['translator'], $app['crud.datafactory'], $entityDefinitionFactory, $app['crud.filesystem'], $validator);
            return $result;
        };

        $twigSetup = new TwigSetup();
        $twigSetup->registerTwigExtensions($app);
    }

    /**
     * Initializes the crud service right after boot.
     *
     * @param Application $app
     * the Application instance of the Silex application
     */
    public function boot(Application $app)
    {
        $this->initMissingServiceProviders($app);
        $this->initLocales($app);
    }

}
