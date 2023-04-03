<?php

/**
 * This file is part of a Spipu Bundle
 *
 * (c) Laurent Minguet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spipu\UiBundle\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;

trait UiWebTestCaseTrait
{
    protected function adminLogin(KernelBrowser $client, string $neededMenuLabel): Crawler
    {
        // Home page not logged
        $crawler = $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Log Out")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("' . $neededMenuLabel . '")')->count());

        // Login page
        $crawler = $client->clickLink("Log In");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Log In")')->count());

        // Login
        $client->submit(
            $crawler->selectButton('Log In')->form(),
            [
                '_username' => 'admin',
                '_password' => 'password'
            ]
        );
        $this->assertTrue($client->getResponse()->isRedirect());

        // Home page logged with "Configuration" access
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log Out")')->count());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("' . $neededMenuLabel . '")')->count());

        return $crawler;
    }
}
