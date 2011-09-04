<?php

namespace Phpbb\Area51Bundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');

        $this->assertContains('Get Involved', $crawler->filter('#content h2')->text());
    }

    /**
     * @dataProvider explosionProvider
     */
    public function testExplosions($path)
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', $path);
    }

    public function explosionProvider()
    {
        return array(
            array('/'),
            array('/stats/'),
            // this one is veeeery slow
            // array('/contributors/'),
        );
    }
}
