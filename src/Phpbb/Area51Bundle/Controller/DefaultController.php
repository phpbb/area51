<?php

namespace Phpbb\Area51Bundle\Controller;

use Phpbb\Area51Bundle\PhpbbArea51Bundle;
use Phpbb\Area51Bundle\TrackerChartFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="index")
     * @Template()
     */
    public function indexAction()
    {
        return array(
            'active_tab'    => 'index',
        );
    }

    /**
     * @Route("/stats/", name="stats")
     * @Template()
     */
    public function statsAction()
    {
        $trackerStart = new \DateTime('2006-01-01T00:00:00+00:00');

        /** @var TrackerChartFactory $factory */
        $factory = $this->get('tracker_chart_factory');

        $ascraeusCreatedVsResolved = $factory->create()
            ->selectAscraeus()
            ->createdVsResolved()
            ->daysSince($trackerStart)
            ->quarterly()
            ->cumulative(true)
            ->showUnresolvedTrend()
            ->get();

        $ascraeusAvgAge = $factory->create()
            ->selectAscraeus()
            ->averageAge()
            ->daysSince($trackerStart)
            ->monthly()
            ->get();

        $rheaCreatedVsResolved = $factory->create()
            ->selectRhea()
            ->createdVsResolved()
            ->daysSince($trackerStart)
            ->quarterly()
            ->cumulative(true)
            ->showUnresolvedTrend()
            ->get();

        $rheaAvgAge = $factory->create()
            ->selectRhea()
            ->averageAge()
            ->daysSince($trackerStart)
            ->monthly()
            ->get();

        return array(
            'active_tab'                    => 'stats',

            'ascraeus_created_vs_resolved'  => $ascraeusCreatedVsResolved,
            'ascraeus_avg_age'              => $ascraeusAvgAge,
            'rhea_created_vs_resolved'      => $rheaCreatedVsResolved,
            'rhea_avg_age'                  => $rheaAvgAge,
        );
    }

    /**
     * @Route("/projects/", name="projects")
     * @Template()
     */
    public function projectsAction()
    {
        return array(
            'active_tab'    => 'projects',
        );
    }

    /**
     * @Route("/docs{path}", requirements={"path"=".*"}, defaults={"path"=""})
     */
    public function docsRedirectAction($path)
    {
        if (preg_match('#^/3#', $path)) {
            return $this->redirect('http://area51.phpbb.com/docs/30x'.$path, 301);
        }
        return $this->redirect($this->generateUrl('index'), 301);
    }

    /**
     * @Route("/downloads/", name="downloads")
     * @Template()
     */
    public function downloadsAction()
    {
        // Make this false when the most recent release is not an RC/Alpha/Beta
        $latestDevelopment = false;

        $previousVersions = ['3.2.7', '3.2.6', '3.2.5', '3.2.4', '3.2.3', '3.2.2', '3.2.1', '3.2.0', '3.1.12', '3.1.11', '3.1.10', '3.1.9', '3.1.8', '3.1.7-pl1', '3.1.6', '3.1.5', '3.1.4', '3.1.3', '3.1.2', '3.1.1', '3.1.0'];
        $currentVersion = '3.2.8-RC1';
        $mainPackageSha = '01ce57f0ecf123ca78d321d4156fc10cce1b325777db1845c719b4d814c2e83b';
        $currentBranch = '3.2/unstable';
        $currentVersionFiles =  'https://download.phpbb.com/pub/release/' . $currentBranch
            . '/' . $currentVersion . '/';
        $currentUpgradeFiles =  'https://download.phpbb.com/pub/release/' . $currentBranch
            . '/' . $currentVersion . '/';

        return array(
            'active_tab'            => 'downloads',
            'latestDevelopment'     => $latestDevelopment,
            'previousVersions'       => $previousVersions,
            'currentVersion'        => $currentVersion,
            'currentVersionFiles'   => $currentVersionFiles,
            'currentUpgradeFiles'   => $currentUpgradeFiles,
            'mainPackageSha'    => $mainPackageSha,
        );
    }
}
