<?php
/**
* @covers KMM\Flattable\Core
*/
use KMM\Flattable\Core;

class FlattableTestDB
{
    public $prefix = "wptest";

    public function query($sql)
    {
    }
}

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
    public function delete_post_not_enabled_post()
    {
        $post_id = $this->factory->post->create(['post_type' => "test"]);

        //Mock the DB
        $mock = $this->getMockBuilder('KMM\\Flattable\\Core\\FlattableTestDB')
        ->setMethods(array( 'query' ))
        ->getMock();

        $mock->prefix = "wptest";

        //Expect query not sent
        $mock->expects($this->never())
            ->method('query')
            ->with("delete from wptestflattable_test where post_id=" . $post_id);

        $this->core->wpdb = $mock;

        $this->core->delete_post($post_id);
    }

    /**
    * @test
    */
    public function save_post_no_post()
    {
        $save = $this->core->save_post(12, null, false);
        $this->assertNull($save);
    }

    /**
    * @test
    */
    public function checkTable()
    {
        $post_id = $this->factory->post->create(['post_type' => "test"]);
        $post = get_post($post_id);
        $postType = $post->post_type;
        $columns = [
            ["column" => "post_id", "type" =>  "int(12)"],
            ["column" => "post_type", "type" =>  "varchar(100)"],
        ];
        $check = $this->core->checkTable($postType, $columns);
        $this->assertTrue($check);
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}