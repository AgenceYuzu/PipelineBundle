<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Yuzu\PipelineBundle\FeedbackForm;

use Symfony\Component\Form\Form;
use Yuzu\PipelineBundle\Entity\Feedback;

interface FeedbackFormProvider
{
    /**
     * @param Feedback $feedback
     * @return Form
     */
    public function getFeedbackForm(Feedback $feedback);
}
