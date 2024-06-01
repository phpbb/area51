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

        $this->assertStringContainsString('Get Involved', $crawler->filter('#content h2')->text());
        $this->assertEquals(200, $response->getStatusCode(), 'Response Code Check');
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
            $this->assertEquals(200, $response->getStatusCode(), 'Response Code Check');
        }
    }

    public function explosionProvider(): array
    {
        return [
            ['/'],
            ['/stats/'],
            ['/downloads/'],
            ['/projects/'],
            //array('/contributors/'),
        ];
    }
}
