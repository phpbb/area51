<?php

namespace Phpbb\Area51Bundle;

class TrackerChart
{
	protected $root_path;
	protected $jira;
	protected $cacheInterval = 3600;
	protected $query;
	protected $gadgetName;

	public function __construct($root_path)
	{
		$this->root_path = $root_path;
		$this->jira = 'http://tracker.phpbb.com/';

		$this->query['versionLabels'] = 'all';
		$this->query['selectedProjectId'] = '10010';

		$this->query['width'] = '380';
		$this->query['height'] = '300';
		$this->query['timestamp-cache-expire'] = date('Y-z');
	}

	public function width($width)
	{
		$this->query['width'] = $width;
		return $this;
	}

	public function height($height)
	{
		$this->query['height'] = $height;
		return $this;
	}

	public function showUnresolvedTrend()
	{
		$this->query['showUnresolvedTrend'] = 'true';
		return $this;
	}

	public function createdVsResolved()
	{
		//$this->query['reportKey'] = 'com.atlassian.jira.plugin.system.reports:createdvsresolved-report';
		$this->gadgetName = 'createdVsResolved';
		return $this;
	}

	public function resolutionTime()
	{
		$this->gadgetName = 'resolutiontime';
		return $this;
	}

	public function averageAge()
	{
		$this->gadgetName = 'averageage';
		return $this;
	}

	public function authorPieChart($stat)
	{
		$this->gadgetName = 'piechart';
		$this->data['statType'] = $stat;
		return $this;
	}

	public function selectOlympus()
	{
		$this->query['projectOrFilterId'] = 'filter-10201';
		return $this;
	}

	public function selectAscraeus()
	{
		$this->query['projectOrFilterId'] = 'filter-10202';
		return $this;
	}

	public function selectAscraeusResolved()
	{
		$this->query['projectOrFilterId'] = 'filter-10203';
		return $this;
	}

	public function days($days)
	{
		$this->query['daysprevious'] = $days;
		return $this;
	}

	public function daysSince(\DateTime $date)
	{
		$this->query['daysprevious'] = $date->diff(new \DateTime())->days;
		return $this;
	}

	public function cumulative($cumulative)
	{
		$this->query['cumulative'] = 'true';
		return $this;
	}

	public function yearly()
	{
		$this->query['periodName'] = 'yearly';
		$this->cacheInterval = 24 * 60 * 60; // one day
		return $this;
	}

	public function quarterly()
	{
		$this->query['periodName'] = 'quarterly';
		$this->cacheInterval = 24 * 60 * 60; // one day
		return $this;
	}

	public function monthly()
	{
		$this->query['periodName'] = 'monthly';
		$this->cacheInterval = 24 * 60 * 60; // one day
		return $this;
	}

	public function weekly()
	{
		$this->query['periodName'] = 'weekly';
		$this->cacheInterval = 24 * 60 * 60; // one day
		return $this;
	}

	public function daily()
	{
		$this->query['periodName'] = 'daily';
		$this->cacheInterval = 24 * 60; // one hour
		return $this;
	}

	public function get()
	{
		$query = '';
		foreach ($this->query as $key => $value)
		{
			$query .= urlencode($key) . '=' . urlencode($value) . '&';
		}

		$targetPath = $this->root_path . 'writable/stats/' . md5($query) . '.png';


		if (!file_exists($targetPath) || filemtime($targetPath) + $this->cacheInterval < time())
		{
			$this->download($targetPath, $query);
		}

		return $targetPath;
	}

	public function download($targetPath, $query)
	{
		$json = file_get_contents($this->jira . 'rest/gadget/1.0/' . $this->gadgetName . '/generate?' . $query);

		$data = json_decode($json);

		if ($data->location)
		{
			$imgUrl = $this->jira . 'charts?filename=' . $data->location;
			$imgData = file_get_contents($imgUrl);

			if ($imgData)
			{
				file_put_contents($targetPath, $imgData);
			}
		}
	}

	public function render()
	{
		return '<img src="' . $this->get() . '" alt="" title=""/>';
	}
}
