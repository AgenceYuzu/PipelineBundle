<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 09/03/15
 * Time: 10:57
 */

namespace Yuzu\PipelineBundle\FeedbackForm;


use Symfony\Component\Form\FormFactory;
use Yuzu\PipelineBundle\Entity\Feedback;

class SimpleFeedbackFormProvider implements FeedbackFormProvider {

    /**
     * @var FormFactory
     */
    private $factory;

    public function __construct(FormFactory $factory ){

        $this->factory = $factory;
    }

    public function getFeedbackForm(Feedback $feedback)
    {
        $formBuilder = $this->factory->createBuilder('form', $feedback);

        $formBuilder
            ->add('name', 'text')
            ->add('email', 'email')
            ->add('message', 'textarea')
            ->add('save', 'submit')
        ;

        return $formBuilder->getForm();
    }
}