<?php

namespace Ekreative\RedmineBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Date;

class RedmineController extends Controller {

    public function indexAction()
    {
        $redmine = $this->get('ekreative_redmine');
        $projects = $redmine->getProjectList();
//        $projects = array('test', 'test2', 'test3', 'test4', 'test5');

        return $this->render('EkreativeRedmineBundle:Redmine:main.html.twig', array('projects' => $projects->projects));
    }

    public function getIssuesPerProjectAction($projectId, Request $request)
    {
        $page = $request->get('page') ? $request->get('page') : 1;
        $limit = $request->get('limit') ? $request->get('limit') : 25;
        $redmine = $this->get('ekreative_redmine');
        $issues = $redmine->getIssuesPerProject($projectId, $page, $limit);
        $pages = ceil($issues->total_count / $limit);
        $templateData = array(
            'issues'       => $issues->issues,
            'pages'        => $pages,
            'current_page' => $page,
            'limit'        => $limit,
            'projectId'    => $projectId
        );
        return $this->render('EkreativeRedmineBundle:Redmine:issues.html.twig', $templateData);

    }

    public function logTimeAction($projectId, Request $request)
    {
        $defaultData = array(
            'date' => new \DateTime
        );
        $form = $this->createFormBuilder($defaultData)
            ->add('issue_id', 'number', array(
                'label' => 'Issue',
                'required' => false
            ))
            ->add('date', 'date', array(
                'label' => 'Date',
                'input'  => 'datetime',
                'widget' => 'choice',
                'required' => true,
                'constraints' => array(
                    new Date()
                )
            ))
            ->add('hours', 'number', array(
                'label' => 'Hours',
                'rounding_mode' => 2,
            ))
            ->add('comments', 'text', array(
                'label' => 'Comments',
                'required' => true,
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 3))
                )
            ))
            ->add('activity', 'choice', array(
                'required' => true,
                'choices' => array(
                    '8'  => 'Design',
                    '9'  => 'Development',
                    '10' => 'Management',
                    '11' => 'Testing',
                )
            ))
            ->add('create', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $redmine = $this->get('ekreative_redmine');
            $data['project_id'] = $projectId;
            $date = $data['date']->format('Y-m-d');
            $data['date'] = $date;
            $result = $redmine->logTime($data);
//            TODO: handle success and error
            $this->addFlash(
                'success',
                'Successful creation.'
            );
        }  else if ($form->isSubmitted()) {
            $this->addFlash(
                'notice',
                'Your data is not valid!'
            );
        }
        return $this->render('EkreativeRedmineBundle:Redmine:log_time_form.html.twig', array(
            'form' => $form->createView(),
            'project_id' => $projectId
        ));
    }

    public function trackAction()
    {
        $redmine = $this->get('ekreative_redmine');
        $result = $redmine->track();
        $response = new Response($result);
        return $response;
    }

}