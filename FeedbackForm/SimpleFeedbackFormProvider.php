<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Yuzu\PipelineBundle\FeedbackForm;

use Symfony\Component\Form\FormFactory;
use Yuzu\PipelineBundle\Entity\Feedback;

class SimpleFeedbackFormProvider implements FeedbackFormProvider
{
    /** @var \Symfony\Component\Form\FormFactory */
    private $factory;

    /**
     * @param \Symfony\Component\Form\FormFactory $factory
     */
    public function __construct(FormFactory $factory)
    {
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
