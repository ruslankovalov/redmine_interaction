<?php
/**
 * Created by PhpStorm.
 * User: madman
 * Date: 27.03.15
 * Time: 18:37
 */

namespace Ekreative\RedmineBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class CommentType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text')
            ->add('create', 'submit');
    }

    public function getName()
    {
        return 'comment';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ekreative\RedmineBundle\Entity\Comment',
        ));
    }
}