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
}