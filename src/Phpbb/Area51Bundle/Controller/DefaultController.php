<?php

namespace Phpbb\Area51Bundle\Controller;

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

        $factory = $this->get('tracker_chart_factory');

        $olympusCreatedVsResolved = $factory->create()
            ->selectOlympus()
            ->createdVsResolved()
            ->daysSince($trackerStart)
            ->quarterly()
            ->cumulative(true)
            ->showUnresolvedTrend()
            ->get();

        $olympusAvgAge = $factory->create()
            ->selectOlympus()
            ->averageAge()
            ->daysSince($trackerStart)
            ->monthly()
            ->get();

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

        return array(
            'active_tab'                    => 'stats',

            'olympus_created_vs_resolved'   => $olympusCreatedVsResolved,
            'olympus_avg_age'               => $olympusAvgAge,
            'ascraeus_created_vs_resolved'  => $ascraeusCreatedVsResolved,
            'ascraeus_avg_age'              => $ascraeusAvgAge,
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

        $previousVersions = ['3.2.4', '3.2.3', '3.2.2', '3.2.1', '3.2.0', '3.1.12', '3.1.11', '3.1.10', '3.1.9', '3.1.8', '3.1.7-pl1', '3.1.6', '3.1.5', '3.1.4', '3.1.3', '3.1.2', '3.1.1', '3.1.0'];
        $currentVersion = '3.2.5-RC1';
        $mainPackageSha = '8d70bd6d9a5f6aca5fe973e76a1a142d9d92f7e5d694d84aa8fce6c7d697d1ae';
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
