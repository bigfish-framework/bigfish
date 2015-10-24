<?php
/**
 * BigFish unit tests.
 *
 * @copyright  Copyright (c) 2015 BigFish
 * @licence    MIT
 */
namespace BigFish;

use BigFish\Services\Service;

class CardboardBoxTest extends \PHPUnit_Framework_TestCase {

    public function setUp() {
        // ...
    }

    /**  */
    public function testItShouldSetAndGetParameters() {
        $app = new CardboardBox(['debug' => true]);
        $values = [
            'app.name' => 2,
            'app.bar.foo' => 3,
            'app.bar.baz' => 4,
            'app.foo.bar.baz' => 5,
            'app.foo.bar.bing' => 6,
            'foo.bar.baz.bing.bong' => 7,
        ];
        $all = [
            'debug' => true,
            'app' => [
               'name' => 2,
               'bar' => ['foo' => 3, 'baz' => 4],
               'foo' => ['bar' => ['baz' => 5, 'bing' => 6]],
            ],
           'foo' => ['bar' => ['baz' => ['bing.bong' => 7]]],
           'array' => ['first' => 1, 'second => 2'],
        ];

        $app->set($values);
        $app->set('array',  ['first' => 1, 'second => 2']);

        $this->assertEquals(3, $app->get('app.bar.foo'));
        $this->assertEquals($all, $app->get());
    }

    /**
     * @expectedException BigFish\Exception
     * @expectedExceptionMessageRegExp |^CardboardBox parameter error|
     */
     public function testItShouldThrowAnExceptionOnOverwritingAScalarWithAnArray() {
        $app = new CardboardBox;
        $values = [
            'app.foo' => 2,
            'app.foo.bar.baz' => 5,
        ];

        $app->set($values);
    }

    /**  */
     public function testItShouldLoadAServiceThatHasBeenDefined() {

        $app = new CardboardBox;
        $service = new TestService($app);

        // test we can set it directly
        $app->testDirect = $service;
        $this->assertEquals($service, $app->testDirect);

        // test it loads a class of the same type
        $app->testLazy = TestService::class;
        $lazy = $app->testLazy;
        $this->assertEquals($service, $lazy);

        // test it is a singleton
        $lazy2 = $app->testLazy;
        $this->assertTrue($lazy === $lazy2);
    }

    /**  */
     public function testItShouldThrowExceptionsForBadlyDefinedServices() {

        $app = new CardboardBox;

        // test undefined service
        try {
            $app->undefinedService;
        } catch (Exception $e) {
            $this->assertRegExp('|undefinedService|', $e->getMessage());
        }

        // test nonexistant class
        try {
            $app->nonexistantClass = NonexistantClass::class;
            $app->nonexistantClass;
        } catch (Exception $e) {
            $this->assertRegExp('|nonexistantClass|', $e->getMessage());
        }

        // test class that throws an exception
        try {
            $app->classWithError = TestServiceWithError::class;
            $app->classWithError;
        } catch (Exception $e) {
            $this->assertRegExp('|classWithError|', $e->getMessage());
        }
    }
}

class TestService extends Service {
}

class TestServiceWithError extends Service {
    public function __construct(Service $app) {
        // this will break because services are instantiated with a CardboardBox object
    }
}
