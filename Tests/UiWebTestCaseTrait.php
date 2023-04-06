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
use Symfony\Component\DomCrawler\Form;

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

    /**
     * @param Crawler $crawler
     * @return array
     */
    protected function getGridConfigDisplayList(Crawler $crawler): array
    {
        $configOptions = $crawler->filter('select[data-grid-role=config-select] option');
        $options = [];
        $configOptions->each(function (Crawler $configOption) use (&$options) {
            $options[strtolower(trim($configOption->text()))] = [
                'id' => (int) $configOption->attr('value'),
                'selected' => !empty($configOption->attr('selected')),
            ];
        });
        ksort($options);
        return $options;
    }

    protected function submitGridQuickSearch(
        KernelBrowser $client,
        Crawler $crawler,
        string $field,
        string $value
    ): Crawler
    {
        $buttonSelector = 'button:contains("Search")';

        $this->assertGreaterThan(0, $crawler->filter($buttonSelector)->count());

        $crawler = $client->submit($crawler->selectButton('Search')->form(), ['qs[field]' => $field, 'qs[value]' => $value]);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(0, $crawler->filter($buttonSelector)->count());

        return $crawler;
    }

    protected function submitGridFilter(
        KernelBrowser $client,
        Crawler $crawler,
        array $filters
    ): Crawler
    {
        $buttonSelector = 'button:contains("Advanced Search")';

        $this->assertGreaterThan(0, $crawler->filter($buttonSelector)->count());

        $crawler = $client->submit($crawler->selectButton('Advanced Search')->form(), $filters);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(0, $crawler->filter($buttonSelector)->count());

        return $crawler;
    }

    protected function submitGridConfigWithWrongValues(
        KernelBrowser $client,
        Form $form,
        array $values,
        string $errorMessage,
        string $totalLabel,
        ?array $expectedDisplayList = null
    ): Crawler {
        $this->submitFormWithSpecificValues($client, $form, $values);

        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasAlert($crawler, $errorMessage);
        $this->assertSame($totalLabel, $crawler->filter('span[data-grid-role=total-rows]')->text());

        if ($expectedDisplayList !== null) {
            $this->assertSame($expectedDisplayList, $this->getGridConfigDisplayList($crawler));
        }

        return $crawler;
    }

    protected function submitGridConfigWithGoodValues(
        KernelBrowser $client,
        Form $form,
        array $values,
        string $totalLabel,
        ?array $expectedDisplayList = null
    ): Crawler {
        $this->submitFormWithSpecificValues($client, $form, $values);

        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasNoAlert($crawler);
        $this->assertSame($totalLabel, $crawler->filter('span[data-grid-role=total-rows]')->text());

        if ($expectedDisplayList !== null) {
            $this->assertSame($expectedDisplayList, $this->getGridConfigDisplayList($crawler));
        }

        return $crawler;
    }

    protected function submitFormWithSpecificValues(KernelBrowser $client, Form $form, array $values): Crawler
    {
        $method = $form->getMethod();

        if (!\in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            foreach ($form->all() as $field) {
                $form->remove($field->getName());
            }

            $uri = $form->getUri();
            $query = parse_url($uri, \PHP_URL_QUERY);
            $currentParameters = [];
            if ($query) {
                parse_str($query, $currentParameters);
            }

            $queryString = http_build_query(array_merge($currentParameters, $values), '', '&');

            $pos = strpos($uri, '?');
            $base = false === $pos ? $uri : substr($uri, 0, $pos);
            $uri = rtrim($base . '?' . $queryString, '?');
            $phpValues = [];
        } else {
            $uri = $form->getUri();

            $tempValues = [];
            foreach ($values as $name => $value) {
                $qs = http_build_query([$name => $value], '', '&');
                if (!empty($qs)) {
                    parse_str($qs, $expandedValue);
                    $varName = substr($name, 0, \strlen(key($expandedValue)));
                    $tempValues[] = [$varName => current($expandedValue)];
                }
            }

            $phpValues = array_replace_recursive([], ...$tempValues);
        }

        return $client->request($method, $uri, $phpValues);
    }
}
