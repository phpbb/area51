<?php

namespace Phpbb\Area51Bundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');
        $response = $client->getResponse();

        $this->assertContains('Get Involved', $crawler->filter('#content h2')->text());
        $this->assertEquals($response->getStatusCode(), 200, 'Response Code Check');
    }

    /**
     * @dataProvider explosionProvider
     */
    public function testExplosions($path, $check = true)
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', $path);
        $response = $client->getResponse();

        if ($check)
        {
            $this->assertEquals($response->getStatusCode(), 200, 'Response Code Check');
        }
    }

    public function explosionProvider()
    {
        return array(
            array('/'),
            array('/stats/', false),
            array('/downloads/'),
            array('/projects/'),
            //array('/contributors/'),
        );
    }
}
