<?php
namespace CCV\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ScriptType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
                        'constraints' => array(
                                new Assert\NotBlank(),
                            	new Assert\Length(array('min' => 3, 'max' => 255)),
                            ),
                        'label' => 'form.title',
                    ));
        $builder->add('contents', 'textarea', array(
                        'constraints' => array(
                                new Assert\NotBlank()
                                ),
                        'label' => 'form.script',
                        'required' => false,
                    ));
    }

    public function getName()
    {
        return 'script';
    }
}