<?php
/**
 * Joomla! Yireo Library
 *
 * @author    Yireo (http://www.yireo.com/)
 * @package   YireoLib
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com/
 * @version   0.6.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Home View class
 *
 * @package Yireo
 */
class YireoViewHomeAjax extends YireoView
{
	/*
	 * Identifier of the library-view
	 */
	protected $_viewParent = 'home';

	/*
	 * Display method
	 *
	 * @param string $tpl
	 * @return null
	 */
	public function display($tpl = null)
	{
		switch ($this->input->get('layout'))
		{
			case 'feeds':
				$this->feeds = $this->fetchFeeds('https://www.yireo.com/blog?format=feed&type=rss', 3);
				break;

			case 'promotion':
				$html = YireoHelper::fetchRemote('https://www.yireo.com/advertizement.php', $this->getConfig('option'));
				print $html;
				exit;
		}

		parent::display($tpl);
	}

	/*
	 * Display method
	 *
	 * @param string $url
	 * @param int $max
	 * @return array
	 */
	public function fetchFeeds($url = '', $max = 3)
	{
		ini_set('display_errors', 0);
		$remote = YireoHelper::fetchRemote($url);

		if (empty($remote))
		{
			return false;
		}

		$xml = simplexml_load_string($remote);

		if (!$xml)
		{
			return false;
		}

		$feeds  = array();
		$i = 0;

		foreach ($xml->channel->item as $item)
		{
			if ($i == $max)
			{
				break;
			}

			$feed                = array();
			$feed['link']        = (string) $item->link;
			$feed['title']       = (string) $item->title;
			$feed['description'] = preg_replace('/<img([^>]+)>/', '', (string) $item->description);
			$feeds[]             = $feed;
			$i++;
		}

		return $feeds;
	}
}