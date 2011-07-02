<?php

namespace Phpbb\Area51Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
     * @Route("/stats", name="stats")
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
            ->selectAscraeusResolved()
            ->authorPieChart('assignees')
            ->daysSince($trackerStart)
            ->showUnresolvedTrend()
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
     * @Route("/docs{path}", requirements={"path"=".*"}, defaults={"path"=""})
     */
    public function docsRedirectAction($path)
    {
        if (preg_match('#^/3#', $path)) {
            return $this->redirect('http://area51.phpbb.com/docs/30x'.$path, 301);
        }
        return $this->redirect($this->generateUrl('index'), 301);
    }
}
