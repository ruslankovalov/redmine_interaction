<?php

namespace Ekreative\RedmineBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Ekreative\RedmineBundle\Entity\Comment;
use Ekreative\RedmineBundle\Form\Type\CommentType;


class RedmineController extends Controller {

    public function indexAction()
    {
        $redmine = $this->get('ekreative_redmine');
        $projects = $redmine->getProjectList();

        return $this->render('EkreativeRedmineBundle:Redmine:main.html.twig', array('projects' => $projects->projects));
    }

    public function issuesAction($projectId, Request $request)
    {
        $page = $request->get('page') ? $request->get('page') : 1;
        $limit = $request->get('limit') ? $request->get('limit') : 25;
        $redmine = $this->get('ekreative_redmine');
        $project = $redmine->getProject($projectId);
        $issues = $redmine->getIssues($projectId, $page, $limit);
        $pages = ceil($issues->total_count / $limit);
        $templateData = array(
            'issues'       => $issues->issues,
            'pages'        => $pages,
            'current_page' => $page,
            'limit'        => $limit,
            'project'    => $project->project
        );

        return $this->render('EkreativeRedmineBundle:Redmine:project.html.twig', $templateData);
    }

    public function commentsAction($projectId)
    {
        $redmine = $this->get('ekreative_redmine');
        $project = $redmine->getProject($projectId);
        $repository = $this->getDoctrine()
            ->getRepository('EkreativeRedmineBundle:Comment');
        $comments = $repository->findBy(
            array('projectId' => $projectId)
        );

        return $this->render('EkreativeRedmineBundle:Redmine:comments.html.twig', array(
            'comments'   => $comments,
            'project' => $project->project
        ));
    }

    public function commentAction($projectId, Request $request)
    {
        $redmine = $this->get('ekreative_redmine');
        $project = $redmine->getProject($projectId);
        $comment = new Comment();
        $comment->setProjectId((int) $projectId);
        $comment->setCreatedAt(new \DateTime());
        $comment->setUpdatedAt(new \DateTime());
        $comment->setAuthor($this->getUser()->getUsername());

        $form = $this->createForm(new CommentType(), $comment);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();
            $this->addFlash(
                'success',
                'Comment is successfully saved'
            );

            return $this->redirectToRoute('comments', array('projectId' => $projectId));

        } else if ($form->isSubmitted()) {
            $this->addFlash(
                'notice',
                'Your data is not valid!'
            );
        }

        return $this->render('EkreativeRedmineBundle:Redmine:form.html.twig', array(
            'form' => $form->createView(),
            'project' => $project->project
        ));
    }

    public function logTimeAction($projectId, Request $request)
    {
        $redmine = $this->get('ekreative_redmine');
        $project = $redmine->getProject($projectId);
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
                    new DateTime()
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
            ->add('activity_id', 'choice', array(
                'label' => 'Activity',
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
            $date = $data['date'];
            $result = $redmine->logTime($data);

            if (isset($result->errors)) {
                $errors = $result->errors;
                $this->addFlash('error', 'Your request has failed. Please try again later.');
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
            } else {
                $this->addFlash('success', 'Time successfully logged.');
            }

            return $this->redirectToRoute('issues', array('projectId' => $projectId));

        }  else if ($form->isSubmitted()) {
            $this->addFlash(
                'notice',
                'Your data is not valid!'
            );
        }

        return $this->render('EkreativeRedmineBundle:Redmine:form.html.twig', array(
            'form' => $form->createView(),
            'project' => $project->project
        ));
    }
}