<?php
/**
* @covers \KMM\Flattable\Core
*/
use KMM\Flattable\Core;
use phpmock\MockBuilder;

class FlattableTestDB {
    public $prefix = 'wptest';

    public function query($sql) {
    }

    public function get_results($r) {
    }

    public function prepare($data) {
    }
}

class TestFlattable extends \WP_UnitTestCase {
    public function setUp(): void {
        // setup a rest server
        parent::setUp();
        $this->core = new Core('i18n');
        add_filter('krn_flattable_values_article', function () {
            return ['x' => 1];
        });
        add_filter('krn_flattable_columns_article', function () {
            return [
                      ['column' => 'x', 'type' => 'int(1)', 'printf' => '%d'],
              ];
        });
    }

    /**
     * @test
     */
    public function rest_update_no_wp_post() {
        $response = $this->core->rest_update(null, null, false);
        $this->assertNull($response);
    }

    /**
     * @test
     */
    public function delete_not_enabled_post() {
        $post_id = $this->factory->post->create(['post_type' => 'test']);

        // Mock the DB
        $mock = $this->getMockBuilder('KMM\\Flattable\\KMM\\FlattableTestDB')
            ->setMethods(['query'])
            ->getMock();

        $mock->prefix = 'wptest';

        // Expect query not sent
        $mock->expects($this->never())
            ->method('query');

        $this->core->wpdb = $mock;

        $this->core->delete_post($post_id, false);
    }

    /**
     * @test
     */
    public function delete_enabled_post() {
        $post_id = $this->factory->post->create(['post_type' => 'article', 'post_password' => '']);
        $postObj = get_post($post_id);

        // Mock the DB
        $mock = $this->getMockBuilder('KMM\\Flattable\\Core\\FlattableTestDB')
            ->setMethods(['query'])
            ->getMock();

        $mock->prefix = 'wptest';

        // Expect query sent
        $mock->expects($this->once())
            ->method('query')
            ->with('delete from wptestflattable_article where post_id=' . $post_id);

        $this->core->wpdb = $mock;

        // Check if krn_flattable_pre_delete action is called correctly
        $add_action = new MockBuilder();
        $cp = $this;
        $add_action->setNamespace("\KMM\Flattable")
                ->setName('do_action')
                ->setFunction(function ($a) use ($cp) {
                    $cp->assertEquals($a, 'krn_flattable_pre_delete_article');

                    return $a;
                });
        $add_action_mock = $add_action->build();
        $add_action_mock->enable();

        $this->core->delete_post($post_id, true);

        $add_action_mock->disable();
    }

    /**
     * @test
     */
    public function save_post_no_post() {
        $save = $this->core->save_post(12, null, false, false);
        $this->assertNull($save);
    }

    /**
     * @test
     */
    public function save_enabled_post_insert_update() {
        $post_id = $this->factory->post->create(['post_type' => 'article', 'post_password' => '']);
        $postObject = get_post($post_id);

        // Mock the DB
        $mock = $this->getMockBuilder('KMM\\Flattable\\Core\\FlattableTestDB')
            ->setMethods(['query', 'get_charset_collate', 'get_results', 'suppress_errors', 'get_row', 'prepare'])
            ->getMock();

        $mock->prefix = 'wptest';

        // Expect query sent
        $mock->expects($this->exactly(4))
            ->method('query')
            ->withConsecutive(['ALTER TABLE wptestflattable_article ADD `post_id` int(12)'], ['ALTER TABLE wptestflattable_article ADD `post_type` varchar(100)'], ['ALTER TABLE wptestflattable_article ADD `x` int(1)']);

        $this->core->wpdb = $mock;

        $this->core->save_post($post_id, $postObject, false, true);
    }

    /**
     * @test
     */
    public function checkTable() {
        $post_id = $this->factory->post->create(['post_type' => 'test']);
        $post = get_post($post_id);
        $postType = $post->post_type;
        $columns = [
            ['column' => 'post_id', 'type' => 'int(12)'],
            ['column' => 'post_type', 'type' => 'varchar(100)'],
        ];

        // Mock the DB
        $mock = $this->getMockBuilder('KMM\\Flattable\\Core\\FlattableTestDB')
            ->setMethods(['get_charset_collate', 'get_results', 'suppress_errors', 'query'])
            ->getMock();

        $mock->prefix = 'wptest';

        // Expect query sent
        $mock->expects($this->any())
            ->method('suppress_errors')
            ->willReturn(true);

        // Expect query sent
        $mock->expects($this->exactly(2))
            ->method('query')
            ->withConsecutive(['ALTER TABLE wptestflattable_test ADD `post_id` int(12)'], ['ALTER TABLE wptestflattable_test ADD `post_type` varchar(100)']);

        // Expect query sent
        $mock->expects($this->any())
            ->method('suppress_errors')
            ->willReturn(false);

        $this->core->wpdb = $mock;

        $check = $this->core->checkTable($postType, $columns);
        $this->assertTrue($check);
    }

    public function tearDown(): void {
        parent::tearDown();
    }
}
