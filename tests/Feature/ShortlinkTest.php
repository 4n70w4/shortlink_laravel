<?php

namespace Tests\Feature;

use App\Link;
use Carbon\Carbon;
use Tests\TestCase;

class ShortlinkTest extends TestCase {

    public function setUp() {
        parent::setUp();

        $mock = \Mockery::mock(Carbon::class);
        $mock->shouldReceive('now')->andReturn( Carbon::create(2018, 3, 14, 17, 49, 2, 4) );
        $mock->shouldReceive('format')->andReturn(1521038942);
        $this->app->instance(Carbon::class, $mock);
    }



    public function testHome() {
        $response = $this->get('/');
        $response->assertStatus(200);
    }


    /**
     * @dataProvider providerFormWrong
     */
    public function testFormWrong($link, $lifetime) {
        $response = $this->post('/', ['link' => $link, 'lifetime' => $lifetime]);
        $response->assertStatus(302);
    }



    /**
     * @dataProvider providerFormCorrect
     */
    public function testFormCorrect($link, $lifetime) {

        $mock = \Mockery::mock(Link::class);
        $mock->shouldReceive('create')->once()->andReturn((object) ['id' => 1]);
        $this->app->instance(Link::class, $mock);

        $response = $this->post('/', ['link' => $link, 'lifetime' => $lifetime]);
        $response->assertStatus(200);
    }



    /**
     * @dataProvider providerRedirect
     */
    public function testRedirect($hash, $shortcode, $decode, $status) {

        $mock = \Mockery::mock(Link::class);
        $mock->shouldReceive('whereId')->andReturn($shortcode ? collect([(object) $shortcode]) : collect(null));
        $this->app->instance(Link::class, $mock);

        $mock = new \Hashids();
        $mock->shouldReceive('decode')->once()->andReturn($decode);
        $this->app->instance(\Hashids::class, $mock);

        $response = $this->get("/{$hash}");

        $response->assertStatus($status);

        if(302 === $response->getStatusCode()) {
            $response->assertRedirect($shortcode['link']);
        }
    }



    public function providerFormWrong() {
        return [
            ['', ''],
            ['google.com', ''],
            ['', 'test'],
            ['', 'now'],
            ['', strtotime('-1 hour')],
            ['', date('Y-m-d H:i:s', strtotime('-1 hour'))],
            ['', strtotime('+1 hour')],
            ['https://googlgoogle.com/ru/', date('Y-m-d', strtotime('+1 hour'))],
        ];
    }



    public function providerFormCorrect() {
        return [
            ['https://googlgoogle.com/ru/', ''],
            ['https://googlgoogle.com/ru/', date('Y-m-d H:i:s', strtotime('+1 hour'))],
            ['https://googlgoogle.com/ru/', date('Y-m-d H:i', strtotime('+1 hour'))],
            ['https://googlgoogle.com/ru/', date('Y-m-d', strtotime('+1 day'))],
        ];
    }



    public function providerRedirect() {
        //  [$hash, $link, $lifetime, array $decode, $status]

        return [
            ['test', ['link' => 'https://googlgoogle.com/ru/', 'lifetime' => 0], null, 400],
            ['test', null, [1, 0], 404],
            ['test', ['link' => 'https://googlgoogle.com/ru/', 'lifetime' => 0], [1, 1521038941], 403],

            ['test', ['link' => 'https://googlgoogle.com/ru/', 'lifetime' => 0], [1, 1521038942], 302],
            ['test', ['link' => 'https://googlgoogle.com/ru/', 'lifetime' => 0], [1, 1521038943], 302],
        ];
    }



}
