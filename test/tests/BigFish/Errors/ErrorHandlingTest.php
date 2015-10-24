<?php
/**
 * BigFish unit tests.
 *
 * @copyright  Copyright (c) 2015 BigFish
 * @licence    MIT
 */

namespace BigFish\Errors;

use BigFish\CardboardBox;
use BigFish\Exception;

class ErrorHandlingTest extends \PHPUnit_Framework_TestCase {

    /**  */
    public function setUp() {
        $app = new CardboardBox;
        $this->h = new ErrorHandling($app);
    }

    /**
     * @expectedException BigFish\Exception
     * @expectedExceptionMessage Test
     */
    public function testHandleErrorShouldThrowAnException() {
        $this->h->handleError(0, 'Test', '', 0);
    }

    /**  */
    public function testHandleExceptionShouldRenderAnException() {
        $e = new \Exception('Test Exception');

        ob_start();
        $this->h->handleException($e);
        
        $this->assertRegExp('|Test Exception|', ob_get_clean());
    }


    /**  */
    public function testConstructorShouldUseStrtrForMessage() {
        $e = new Exception(['Test :e', ':e' => 'Exception']);
        $this->assertEquals('Test Exception', $e->getMessage());

        $e = new Exception(['Test 0', 'Simple']);
        $this->assertEquals('Test Simple', $e->getMessage());

        $e = new Exception(['Test']);
        $this->assertEquals('Test', $e->getMessage());
    }
}
