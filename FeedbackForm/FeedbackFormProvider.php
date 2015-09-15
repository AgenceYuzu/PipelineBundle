<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Yuzu\PipelineBundle\FeedbackForm;

use Yuzu\PipelineBundle\Entity\Feedback;

interface FeedbackFormProvider
{
    /**
     * @param \Yuzu\PipelineBundle\Entity\Feedback $feedback
     *
     * @return \Symfony\Component\Form\Form
     */
    public function getFeedbackForm(Feedback $feedback);
}
