<?php

namespace NMC_Social;

class TimberInterface {

	/**
	 * REST Services Key
	 *
	 * @var string
	 */
	private $key = null;

	/**
	 * Cache Dir
	 */
	private $cacheDir = null;

	public function __construct($key, $cacheDir)
	{
		$this->setKey($key);
		$this->setCacheDir($cacheDir);
	}

	/**
	 * Set Key
	 *
	 * Set the API Key for all requests
	 * to use.
	 key	 * @param  	string $cacheDir
	 * @return  void
	 */
	public function setKey($key)
	{
		$this->key = $key;
	}

	/**
	 * Set Cache Dir
	 *
	 * @param  	string $cacheDir
	 * @return  void
	 */
	public function setCacheDir($cacheDir)
	{
		$this->cacheDir = $cacheDir;
	}

	/**
	 * Make Request
	 *
	 * Make an API request to the REST API.
	 *
	 * @param  	string $uri
	 * @param  	array  $params
	 * @return  array
	 */
	private function makeRestApiRequest($uri, $params = [])
	{
		$params['key'] = $this->key;

		$url = 'https://restservices.epicenter1.com'.$uri.'?'.http_build_query($params);
		$cacheKey = md5($url);

		$data = $this->makeUrlRequest($url);

		$json = @json_decode($data);
	
		return (!$json) ? [] : $json;
	}

	/**
	 * Make URL Request
	 *
	 * Includes option to cache the
	 * data that comes from the URL.
	 *
	 * @return string
	 */
	private function makeUrlRequest($url)
	{
		$cacheKey = md5($url);
		
		if (!file_exists($this->cacheDir)) {
		    mkdir($this->cacheDir, 0755, true);
		}

		$cacheFile = $this->cacheDir.'/'.$cacheKey;

		// If no cache file, or if cache file is old
		if (!file_exists($cacheFile) || (filemtime($cacheFile) < strtotime('30 minutes ago')))
		{
			$ch = curl_init();
		
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

			$data = curl_exec($ch);
			curl_close($ch);

			file_put_contents($cacheFile, $data);
		} else {
			$data = file_get_contents($cacheFile);
		}

		return $data;
	}

	/**
	 * Populate Collection
	 *
	 * Create a collection from a list of items.
	 *
	 * @param  	mixed $items
	 * @param  	NMC_Social\BaseCollection $collection
	 * @param   NMC_Social\Post $post
	 * @param   int|null $limit
	 * @return 	\NMC_Social\BaseCollection
	 */
	private function populateCollection($items, $collection = null, $post = null, $limit = false)
	{
		// Setup collection
		if (!$collection) {
			$collection = new BaseCollection;
		}

		if (!$post) {
			$post = \NMC_Social\Post::class;
		}

		$count = 1;
		foreach ($items as $item) {

			if ($limit !== false and $count > $limit) {
				continue;
			}

			$collection->add(new $post($item));
			$count++;
		}

		return $collection;
	}

	/**
	 * Tweets
	 *
	 * See: https://gitlab.newmediacampaigns.com/apps/restservices/wikis/apis/twitter
	 *
	 * @param   string $url
	 * @param   array $params
	 * @param   int $limit
	 * @return 	\NMC_Social\BaseCollection
	 */
	public function tweets($handle, $params = [], $limit = false)
	{
		$tweets = $this->makeRestApiRequest('/twitter/tweets/'.$handle, $params);
		return $this->populateCollection($tweets, new BaseCollection, \NMC_Social\Posts\Tweet::class, $limit);
	}

	/**
	 * Instagrams:
	 *
	 * See: https://gitlab.newmediacampaigns.com/apps/restservices/wikis/apis/instagram
	 *
	 * @param   string $handle
	 * @param   int $limit
	 * @return 	\NMC_Social\BaseCollection
	 */
	public function instagrams($handle, $limit = false)
	{
		$images = $this->makeRestApiRequest('/instagram/images/'.$handle);
		return $this->populateCollection($images, new BaseCollection, \NMC_Social\Posts\Instagram::class, $limit);
	}

	/**
	 * Facebook Posts:
	 *
	 * See: https://gitlab.newmediacampaigns.com/apps/restservices/wikis/apis/facebook
	 *
	 * @param   string $handle
	 * @param   int $limit
	 * @return 	\NMC_Social\BaseCollection
	 */
	public function facebookPosts($handle, $limit = false)
	{
		$posts = $this->makeRestApiRequest('/facebook/posts/'.$handle);
		return $this->populateCollection($posts, new BaseCollection, \NMC_Social\Posts\FacebookPost::class, $limit);
	}

	/**
	 * Eventbrite Events:
	 *
	 * See: https://gitlab.newmediacampaigns.com/apps/restservices/wikis/apis/facebook
	 *
	 * @param   string $organizerId
	 * @param   int $limit
	 * @return 	\NMC_Social\BaseCollection
	 */
	public function eventbriteEvents($organizerId, $limit = false)
	{
		$posts = $this->makeRestApiRequest('/eventbrite/events/'.$organizerId);
		return $this->populateCollection($posts, new BaseCollection, \NMC_Social\Posts\EventbriteEvent::class, $limit);
	}

	/**
	 * RSS Posts
	 *
	 * @param   string $url
	 * @param   int $limit
	 * @return 	\NMC_Social\BaseCollection
	 */
	public function rss($url, $limit = 10)
	{
		$out = $this->makeUrlRequest($url);
		$xml = new \SimpleXmlElement($out);

		return $this->populateCollection($xml->channel->item, new BaseCollection, \NMC_Social\Posts\RssItem::class, $limit);
	}

}