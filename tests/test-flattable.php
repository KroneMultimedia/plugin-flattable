<?php
/**
 * Class Tests_REST_API_Demo
 *
 * @package Rest_Api_Demo
 */
/**
 * Testcases for the REST API demo plugin
 */

namespace KMM\Flattable;

class TestFlattable extends \WP_UnitTestCase
{
    public function setUp()
    {
        # setup a rest server
        parent::setUp();

        $this->core = new Core('i18n');
    }

    public function test_save_post_no_post()
    {
        $postId = 1;
        $postObject = '';
        $update = true;
        $save = $this->core->save_post($postId, $postObject, $update);
        $this->assertNull($save);
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}