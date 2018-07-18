<?php

/*
 * This file is part of the CRUDlexSilex2 package.
 *
 * (c) Philip Lehmann-Böhm <philip@philiplb.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CRUDlexTests\Silex;

use CRUDlex\Silex\TwigSetup;
use Eloquent\Phony\Phpunit\Phony;
use PHPUnit\Framework\TestCase;
use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;


class TwigSetupTest extends TestCase
{

    public function testRegisterTwigExtensions()
    {
        $app = new Application();
        $app->register(new TwigServiceProvider());
        $app->register(new SessionServiceProvider(), [
            'session.storage' => new MockArraySessionStorage(),
        ]);
        $twigSetup = new TwigSetup();
        $twigSetup->registerTwigExtensions($app);
        $filter = $app['twig']->getFilter('crudlex_arrayColumn');
        $this->assertNotNull($filter);

        $read = call_user_func($filter->getCallable(), [['id' => 1], ['id' => 2], ['id' => 3]], 'id');
        $expected = [1, 2, 3];
        $this->assertSame($expected, $read);

        $filter = $app['twig']->getFilter('crudlex_basename');

        $read = call_user_func($filter->getCallable(), 'http://www.philiplb.de/foo.txt');
        $expected = 'foo.txt';
        $this->assertSame($read, $expected);

        $read = call_user_func($filter->getCallable(), 'foo.txt');
        $expected = 'foo.txt';
        $this->assertSame($read, $expected);

        $read = call_user_func($filter->getCallable(), '');
        $expected = '';
        $this->assertSame($read, $expected);

        $read = call_user_func($filter->getCallable(), null);
        $expected = '';
        $this->assertSame($read, $expected);

        $requestStackMock = Phony::mock('Symfony\\Component\\HttpFoundation\\RequestStack');
        $requestStackMock->getCurrentRequest->returns(new Request());
        unset($app['request_stack']);
        $app['request_stack'] = $requestStackMock->get();
        $filter = $app['twig']->getFunction('crudlex_getCurrentUri');
        $read = call_user_func($filter->getCallable());
        $expected = 'http://:/';
        $this->assertSame($read, $expected);

        $filter = $app['twig']->getFunction('crudlex_sessionGet');
        $read = call_user_func($filter->getCallable(), 'foo', 'bar');
        $expected = 'bar';
        $this->assertSame($read, $expected);

        $filter = $app['twig']->getFunction('crudlex_sessionFlashBagGet');
        $read = call_user_func($filter->getCallable(), 'foo');
        $expected = [];
        $this->assertSame($read, $expected);

    }

}
