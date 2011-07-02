<?php
require '../src/Page.php';
require '../src/TrackerChart.php';

class StatsPage
{
	public function activeTab()
	{
		return 'stats';
	}

	public function render()
	{
		$trackerStart = new DateTime('2006-01-01T00:00:00+00:00');
?>
				<div>

					<h2>Ticket Tracker Statistics for phpBB &quot;3.0&quot; Olympus</h2>
				<ul class="contribution-list">
					<li>
						<b>Created vs. Resolved Tickets</b>
						<?php
							$chart = new TrackerChart('../');
							$chart->selectOlympus()
								->createdVsResolved()
								->daysSince($trackerStart)
								->quarterly()
								->cumulative(true)
								->showUnresolvedTrend()
								->render();
						?>
					</li>
					<li>
						<b>Average Ticket Age</b>
						<?php
							$chart = new TrackerChart('../');
							$chart->selectOlympus()
								->averageAge()
								->daysSince($trackerStart)
								->monthly()
								->render();
						?>
					</li>
				</ul>

				<h2>Ascraeus (3.1) Ticket Tracker Statistics</h2>
				<ul class="contribution-list">
					<li>
						<b>Created vs. Resolved Tickets</b>
						<?php
							$chart = new TrackerChart('../');
							$chart->selectAscraeus()
								->createdVsResolved()
								->daysSince($trackerStart)
								->quarterly()
								->cumulative(true)
								->showUnresolvedTrend()
								->render();
						?>
					</li>
					<li>
						<b>Assignees of resolved tickets</b>
						<?php
							$chart = new TrackerChart('../');
							$chart->selectAscraeusResolved()
								->authorPieChart('assignees')
								->daysSince($trackerStart)
								->showUnresolvedTrend()
								->render();
						?>
					</li>
				</div>
<?php
	}
}

$page = new Page(new StatsPage);
$page->render();
