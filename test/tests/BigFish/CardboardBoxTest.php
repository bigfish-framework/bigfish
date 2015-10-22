<?php

namespace BigFish;

class CardboardBoxTest extends \PHPUnit_Framework_TestCase {

    public function setUp() {
        // ...
    }

    public function testItCanBeLoaded() {
        $app = new CardboardBox;

        $this->assertInstanceOf(CardboardBox::class, $app);
    }

}
