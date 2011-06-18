<?php

class TrackerChart
{
	protected $jira;
	protected $cacheInterval = 3600;
	protected $query;
	protected $gadgetName;

	public function __construct()
	{
		$this->jira = 'http://tracker.phpbb.com/';

		$this->query['versionLabels'] = 'all';
		$this->query['selectedProjectId'] = '10010';

		$this->query['width'] = '380';
		$this->query['height'] = '300';
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

//http://tracker.phpbb.com/plugins/servlet/gadgets/ifr?container=atlassian&mid=2&country=UK&lang=en&view=default&view-params=%7B%22writable%22%3A%22true%22%7D&st=atlassian%3Aga1X%2BpGAnlmRE3JW7XaJ%2FNgg%2FmS1SGxloS6ZXu1ttsTtYRo8sxeO34%2B5IMoh5E1VW9%2BVi4jtSiEOzTSxwB6%2FJDbpOfDGHZ4jVaRb6L4vEV3Lt5YsKZ1Vb6REikyOVkG4ALANJiAo1rxYwnSObeGowYNPr6AEyyn0MazlSzoKNm9D%2BWmESlvYODv6EKV0TaiGDxwNK0qp0RiqnvE8MKMtDVU9MMOCX%2Bd8zmLFOdlaI3MRV0KpN2NV64sl67V5hX1hnLoFXfm9FvxBMpSjLKAetj1%2FkIMblq038SwO55AFnQMJ5KXrb41USZyq0rXmpgwanwCaxyu5E7jrrNKzGWf%2BzH1h9Xw%3D&up_isConfigured=true&up_isPopup=true&up_refresh=false&up_projectOrFilterId=jql-project+%3D+PHPBB3+AND+fixVersion+%3D+%223.0.9-RC2%22+ORDER+BY+status+DESC%2C+priority+DESC&up_daysprevious=30&up_periodName=daily&up_versionLabel=major&up_isCumulative=30&up_showUnresolvedTrend=false&url=http%3A%2F%2Ftracker.phpbb.com%2Frest%2Fgadgets%2F1.0%2Fg%2Fcom.atlassian.jira.gadgets%3Acreated-vs-resolved-issues-chart-gadget%2Fgadgets%2Fcreatedvsresolved-gadget.xml&libs=auth-refresh

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

	public function daysSince(DateTime $date)
	{
		$this->query['daysprevious'] = $date->diff(new DateTime())->days;
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

		$targetPath = 'writable/stats/' . md5($query) . '.png';


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
		echo '<img src="' . $this->get() . '" alt="" title=""/>';
	}
}