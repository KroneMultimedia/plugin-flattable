<?php
/**
 * Class Tests_REST_API_Demo
 *
 * @package Rest_Api_Demo
 */
/**
 * Testcases for the REST API demo plugin
 */

class TestFlattable extends WP_UnitTestCase
{
    public function setUp()
    {
        # setup a rest server
        parent::setUp();
    }

    // dummy test -> fixme
    public function testTrueIsTrue()
    {
        $foo = true;
        $this->assertTrue($foo);
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}