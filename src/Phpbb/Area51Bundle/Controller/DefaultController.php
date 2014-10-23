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
     * @Cache(expires="tomorrow", public=true)
     * @Route("/contributors/list/", name="contributors_list")
     * @Template()
     */
    public function contributorsListAction()
    {
        $api_url = 'https://api.github.com/repos/phpbb/phpbb3/contributors';
        $contributors = json_decode(file_get_contents($api_url), true);

        foreach ($contributors as $i => $contributor)
        {
            $user_api_url = $contributor['url'];
            $user = json_decode(file_get_contents($user_api_url), true);

            $contributors[$i] = array_merge($contributor, array(
                'name'          => isset($user['name']) ? $user['name'] : $contributor['login'],
                'profile_url'   => 'https://github.com/'.$contributor['login'],
                'commits_url'   => 'https://github.com/phpbb/phpbb3/commits?author='.$contributor['login'],
            ));
        }

        return array(
            'contributors'  => $contributors,
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
        $latestDevelopment = true;
		$previousVersions = array('3.0.12', '3.1.0-a1', '3.1.0-a2', '3.1.0-a3', '3.1.0-b1', '3.1.0-b2', '3.1.0-b3', '3.1.0-b4', '3.1.0-RC1', '3.1.0-RC2', '3.1.0-RC3', '3.1.0-RC4', '3.1.0-RC5');
		$currentVersion = '3.1.0-RC6';
		$mainPackageSha = '719a43cae1b771620f3df94bc935c6cee6f201b8641910475886e4c68fa8609b';
		$subsilverSha = 'c28700ddfb832f2eb8214b2ff42ae3c3d019a04804ba47c4c4d5307dd81b88dd';
        $later = true; // True if branch is 3.1 or later
        $currentBranch = '3.1/unstable';
        $currentVersionFiles =  'https://download.phpbb.com/pub/release/' . $currentBranch
            . '/' . $currentVersion . '/';
        $currentUpgradeFiles =  'https://download.phpbb.com/pub/release/' . $currentBranch
            . '/update/to_' . $currentVersion . '/';

        return array(
            'active_tab'            => 'downloads',
            'latestDevelopment'     => $latestDevelopment,
            'previousVersions'       => $previousVersions,
            'currentVersion'        => $currentVersion,
            'currentVersionFiles'   => $currentVersionFiles,
            'currentUpgradeFiles'   => $currentUpgradeFiles,
            'later'               => $later,
            'mainPackageSha'    => $mainPackageSha,
            'subsilverSha'  => $subsilverSha,
        );
    }
}
