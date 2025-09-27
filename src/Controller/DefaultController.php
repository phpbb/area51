<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function indexAction(): Response
    {
        return $this->render('default/index.html.twig', [
            'active_tab'    => 'index',
        ]);
    }

    /**
     * @Route("/stats/", name="stats")
     */
    public function statsAction(\App\TrackerChartFactory $factory): Response
    {
        $trackerStart = new \DateTime('2006-01-01T00:00:00+00:00');

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

        $proteusCreatedVsResolved = $factory->create()
            ->selectProteus()
            ->createdVsResolved()
            ->daysSince($trackerStart)
            ->quarterly()
            ->cumulative(true)
            ->showUnresolvedTrend()
            ->get();

        $proteusAvgAge = $factory->create()
            ->selectProteus()
            ->averageAge()
            ->daysSince($trackerStart)
            ->monthly()
            ->get();

        return $this->render('default/stats.html.twig', [
            'active_tab'                    => 'stats',

            'ascraeus_created_vs_resolved'  => $ascraeusCreatedVsResolved,
            'ascraeus_avg_age'              => $ascraeusAvgAge,
            'rhea_created_vs_resolved'      => $rheaCreatedVsResolved,
            'rhea_avg_age'                  => $rheaAvgAge,
            'proteus_created_vs_resolved'      => $proteusCreatedVsResolved,
            'proteus_avg_age'                  => $proteusAvgAge,
        ]);
    }

    /**
     * @Route("/projects/", name="projects")
     */
    public function projectsAction(): Response
    {
        return $this->render('default/projects.html.twig', [
            'active_tab'    => 'projects',
        ]);
    }

    /**
     * @Route("/docs{path}", requirements={"path"=".*"}, defaults={"path"=""})
     */
    public function docsRedirectAction($path): RedirectResponse
    {
        if (str_starts_with($path, '/3')) {
            return $this->redirect('http://area51.phpbb.com/docs/30x'.$path, 301);
        }
        return $this->redirect($this->generateUrl('index'), 301);
    }

    /**
     * @Route("/downloads/", name="downloads")
     */
    public function downloadsAction(): Response
    {
        // Make this false when the most recent release is not an RC/Alpha/Beta
        $latestDevelopment = true;

        $previousVersions = ['3.3.15', '3.2.10'];
        $currentVersion = '4.0.0-a1';
        $mainPackageSha = 'c78fe91cae5684cd02532ad25df0b80cac5db5bc8deba7b92fd6100bdffea898';
        $currentBranch = '4.0/unstable';
        $currentVersionFiles =  'https://download.phpbb.com/pub/release/' . $currentBranch
            . '/' . $currentVersion . '/';
        $currentUpgradeFiles =  'https://download.phpbb.com/pub/release/' . $currentBranch
            . '/' . $currentVersion . '/';

        return $this->render('default/downloads.html.twig', [
            'active_tab'            => 'downloads',
            'later'                 => false,
            'latestDevelopment'     => $latestDevelopment,
            'previousVersions'       => $previousVersions,
            'currentVersion'        => $currentVersion,
            'currentVersionFiles'   => $currentVersionFiles,
            'currentUpgradeFiles'   => $currentUpgradeFiles,
            'mainPackageSha'    => $mainPackageSha,
        ]);
    }
}
