<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class TextType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text');
        $builder->add('text', 'textarea');
        $builder->add('shareType', 'choice', array(
            'choices' => array(true => 'public', false => 'private'),
            'multiple' => false,
            'expanded' => false,
            'label' => 'sharable'
        ));
    }

    public function getName()
    {
        return 'text_form';
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Claroline\CoreBundle\Entity\Resource\Text',
            'translation_domain' => 'platform'
        );
    }
}