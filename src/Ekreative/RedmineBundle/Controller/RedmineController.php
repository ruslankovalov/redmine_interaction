<?php

namespace Ekreative\RedmineBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RedmineController extends Controller {

    public function indexAction()
    {
        $redmine = $this->get('ekreative_redmine');
        $projects = $redmine->getProjectList();
//        $projects = array('test', 'test2', 'test3', 'test4', 'test5');

        return $this->render('EkreativeRedmineBundle:Redmine:projects.html.twig', array('projects' => $projects->projects));
    }

    public function getIssuesPerProjectAction($projectId, Request $request)
    {
        $page = $request->get('page') ? $request->get('page') : 1;
        $limit = $request->get('limit') ? $request->get('limit') : 25;
        $redmine = $this->get('ekreative_redmine');
        $issues = $redmine->getIssuesPerProject($projectId, $page, $limit);
        $pages = ceil($issues->total_count / $limit);
        $templateData = array(
            'issues' => $issues->issues,
            'pages' => $pages,
            'current_page' => $page,
            'limit' => $limit,
            'projectId' => $projectId
        );
        return $this->render('EkreativeRedmineBundle:Redmine:issuesperproject.html.twig', $templateData);

    }
}