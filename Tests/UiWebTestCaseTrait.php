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

    protected function getGridProperties(Crawler $crawler, string $gridCode): array
    {
        $properties = [
            'count'   => $this->getGridPropertiesCount($crawler, $gridCode),
            'display' => $this->getGridPropertiesDisplayList($crawler, $gridCode),
            'columns' => $this->getGridPropertiesColumns($crawler, $gridCode),
            'rows'    => $this->getGridPropertiesRows($crawler, $gridCode),
        ];

        return $properties;
    }

    protected function getGridPropertiesCount(Crawler $crawler, string $gridCode): array
    {
        $countLabel = trim($crawler->filter("span[data-grid-code=${gridCode}][data-grid-role=total-rows]")->text());

        $countNb = null;
        if ($countLabel === 'No item found') {
            $countNb = 0;
        }
        if ($countLabel === '1 item found') {
            $countNb = 1;
        }
        if (preg_match('/^([0-9]+) items found*/', $countLabel, $match)) {
            $countNb = (int) $match[1];
        }

        return [
            'label' => $countLabel,
            'nb'    => $countNb,
        ];
    }

    protected function getGridPropertiesDisplayList(Crawler $crawler, string $gridCode): array
    {
        $configOptions = $crawler->filter("select[data-grid-code=${gridCode}][data-grid-role=config-select] option");
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

    protected function getGridPropertiesColumns(Crawler $crawler, string $gridCode): array
    {
        $nodes = $crawler->filter("th[data-grid-code=${gridCode}][data-grid-role=header-column]");

        $columns = [];
        $nodes->each(function (Crawler $node) use (&$columns) {
            $cssClass = $node->attr('class');

            $sort = null;
            if ($cssClass && strpos($cssClass, 'sorting_asc') !== false) {
                $sort = 'asc';
            }
            if ($cssClass && strpos($cssClass, 'sorting_desc') !== false) {
                $sort = 'desc';
            }

            $columns[$node->attr('data-grid-field-name')] = [
                'label'    => trim($node->text()),
                'sortable' => ($cssClass && (strpos($cssClass, 'sorting') !== false)),
                'sort'     => $sort,
            ];
        });

        return $columns;
    }

    protected function getGridPropertiesRows(Crawler $crawler, string $gridCode): array
    {
        $rowNodes = $crawler->filter("tr[data-grid-code=${gridCode}][data-grid-role=row]");

        $rows = [];
        $rowNodes->each(function (Crawler $rowNode) use (&$rows) {
            $row = [];
            $rowNode->filter("td[data-grid-field-name]")->each(function (Crawler $colNode) use (&$row) {
                $row[$colNode->attr('data-grid-field-name')] = trim($colNode->text());
            });

            $rows[$rowNode->attr('data-grid-row-id')] = $row;
        });

        return $rows;
    }

    protected function submitGridQuickSearch(
        KernelBrowser $client,
        Crawler $crawler,
        string $field,
        string $value
    ): Crawler {
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
    ): Crawler {
        $buttonSelector = 'button:contains("Advanced Search")';

        $this->assertGreaterThan(0, $crawler->filter($buttonSelector)->count());

        $crawler = $client->submit($crawler->selectButton('Advanced Search')->form(), $filters);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(0, $crawler->filter($buttonSelector)->count());

        return $crawler;
    }

    protected function submitFormWithSpecificValues(
        KernelBrowser $client,
        Form $form,
        array $values
    ): Crawler {
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

        $client->request($method, $uri, $phpValues);

        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        return $crawler;
    }
}
