<?php
namespace Give\Tests\Unit\Framework\QueryBuilder;

namespace Give\Tests\Unit\Framework\QueryBuilder;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\Concerns\CRUD;
use PHPUnit\Framework\TestCase;

/**
 * @since 2.19.0
 *
 * @covers CRUD
 */
final class CRUDTest extends TestCase
{
    /**
     * Truncate posts table to avoid duplicate records
     *
     * @since 2.19.0
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        $posts = DB::prefix('posts');

        DB::query("TRUNCATE TABLE $posts");
    }

    /**
     * @since 2.19.0
     *
     * @return void
     */
    public function testInsertShouldAddRowToDatabase()
    {
        $data = [
            'post_title' => 'Query Builder CRUD test',
            'post_type' => 'crud_test',
            'post_content' => 'Hello World!',
        ];

        DB::table('posts')->insert($data);

        $id = DB::last_insert_id();

        $post = DB::table('posts')
            ->select('post_title', 'post_type', 'post_content')
            ->where('ID', $id)
            ->get();

        $this->assertEquals($data['post_title'], $post->post_title);
        $this->assertEquals($data['post_type'], $post->post_type);
        $this->assertEquals($data['post_content'], $post->post_content);
    }

    /**
     * @unreleased
     */
    public function testInsertIntoShouldAddMultipleRowsToTheDatabase()
    {
        DB::table('posts')->insert(['post_title' => 'Aye Post']);
        $postIdAye = DB::last_insert_id();

        DB::table('posts')->insert(['post_title' => 'Bee Post']);
        $postIdBee = DB::last_insert_id();

        DB::table('postmeta')
            ->insertInto(
                ['post_id', 'meta_key', 'meta_value'],
                DB::table('posts') // SELECT ID AS post_id, (SELECT "postTitle") as meta_key, post_title AS meta_value FROM wp_posts
                    ->select(['ID', 'post_id'])
                    ->selectRaw('(SELECT "postTitle") as meta_key')
                    ->select(['post_title', 'meta_value'])
            );

        $this->assertEquals('Aye Post', get_post_meta($postIdAye, 'postTitle', true));
        $this->assertEquals('Bee Post', get_post_meta($postIdBee, 'postTitle', true));
    }

    /**
     * @since 2.19.0
     *
     * @return void
     */
    public function testUpdateShouldUpdateRowValuesInDatabase()
    {
        $data = [
            'post_title' => 'Query Builder CRUD test',
            'post_type' => 'crud_test',
            'post_content' => 'Hello World!',
        ];

        DB::table('posts')->insert($data);

        $id = DB::last_insert_id();

        $updated = [
            'post_title'   => 'Query Builder CRUD test - UPDATED',
            'post_type'    => 'crud_test-updated',
            'post_content' => 'Hello World! - UPDATED',
        ];

        DB::table('posts')
            ->where('ID', $id)
            ->update($updated);

        $post = DB::table('posts')
            ->select('ID', 'post_title', 'post_type', 'post_content')
            ->where('ID', $id)
            ->get();

        $this->assertEquals($id, $post->ID);
        $this->assertEquals($updated['post_title'], $post->post_title);
        $this->assertEquals($updated['post_type'], $post->post_type);
        $this->assertEquals($updated['post_content'], $post->post_content);
    }

    /**
     * @since 2.19.0
     *
     * @return void
     */
    public function testDeleteShouldDeleteRowInDatabase()
    {
        $data = [
            'post_title' => 'Query Builder CRUD test',
            'post_type' => 'crud_test',
            'post_content' => 'Hello World!',
        ];

        DB::table('posts')->insert($data);

        $id = DB::last_insert_id();

        $post = DB::table('posts')
            ->where('ID', $id)
            ->get();

        $this->assertNotNull($post);

        DB::table('posts')
            ->where('ID', $id)
            ->delete();

        $post = DB::table('posts')
            ->where('ID', $id)
            ->get();

        $this->assertNull($post);
    }

}
