<?php
/**
* @covers KMM\Flattable\Core
*/
use KMM\Flattable\Core;

class TestFlattable extends \WP_UnitTestCase
{
    public function setUp()
    {
        # setup a rest server
        parent::setUp();
        $this->core = new Core('i18n');
    }

    /**
    * @test
    */
    public function rest_update_no_wp_post()
    {
        $response = $this->core->rest_update(null, null, false);
        $this->assertNull($response);
    }

    /**
    * @test
    */
    public function save_post_no_post()
    {
        $save = $this->core->save_post(12, null, false);
        $this->assertNull($save);
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}