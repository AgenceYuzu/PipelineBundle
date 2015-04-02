<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 09/03/15
 * Time: 10:54
 */

namespace Yuzu\PipelineBundle\FeedbackForm;


use Symfony\Component\Form\Form;
use Yuzu\PipelineBundle\Entity\Feedback;

interface FeedbackFormProvider {

    /**
     * @param Feedback $feedback
     * @return Form
     */
    public function getFeedbackForm(Feedback $feedback);

}