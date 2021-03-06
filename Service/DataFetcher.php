<?php

namespace Happyr\Google\AnalyticsBundle\Service;

use Doctrine\Common\Cache\CacheProvider;

/**
 * This service fetches data from the API
 */
class DataFetcher
{
    /**
     * @var \Doctrine\Common\Cache\CacheProvider cache
     */
    protected $cache;

    /**
     * @var \Google_Client client
     */
    protected $client;

    /**
     * @var int profileId
     */
    protected $viewId;

    /**
     * @var int cacheLifetime
     */
    protected $cacheLifetime;

    /**
     * @param CacheProvider $cache
     * @param \Google_Client $client
     * @param int $viewId
     * @param int $cacheLifetime seconds
     */
    public function __construct(CacheProvider $cache, \Google_Client $client=null, $viewId, $cacheLifetime)
    {
        if (!$client) {
            throw new \LogicException('You must install and configure happyr/google-site-authenticator-bundle in order to use the fetching data service.');
        }

        $this->cache = $cache;
        $this->client = $client;
        $this->viewId = $viewId;
        $this->cacheLifetime = $cacheLifetime;
    }

    /**
     * return the page views for the given url
     *
     * @param string $uri
     * @param string $since date on format ('Y-m-d')
     * @param string $regex
     *
     * @return int
     */
    public function getPageViews($uri, $since = null, $regex = '$')
    {
        if (empty($this->viewId)) {
            throw new \LogicException('You need to specify a profile id that we are going to fetch page views from');
        }

        if (!$since) {
            //one year ago
            $since = date('Y-m-d', time() - 86400 * 365);
        }

        //create the cache key
        $cacheKey = md5($uri.$regex.$since);
        $this->cache->setNamespace('PageStatistics.PageViews');
        if (false === $visits = $this->cache->fetch($cacheKey)) {
            //check if we got a token
            if (null === $this->client->getAccessToken()) {
                return 0;
            }

            //remove the app_dev.php
            $uri = str_replace('/app_dev.php/', '/', $uri);

            //fetch result
            try {
                $analytics = new \Google_Service_Analytics($this->client);
                $results = $analytics->data_ga->get(
                    'ga:' . $this->viewId,
                    $since,
                    date('Y-m-d'),
                    'ga:pageviews',
                    array('filters' => 'ga:pagePath=~^' . $uri . $regex )
                );

                $rows = $results->getRows();
                $visits = intval($rows[0][0]);
            } catch (\Google_Auth_Exception $e) {
                $visits = 0;
            }

            //save cache (TTL 1h)
            $this->cache->save($cacheKey, $visits, $this->cacheLifetime);
        }

        return $visits;
    }
}