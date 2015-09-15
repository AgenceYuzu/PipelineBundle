<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Yuzu\PipelineBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use Symfony\Component\HttpFoundation\Request;
use Yuzu\PipelineBundle\Entity\Feedback;
use Symfony\Component\HttpFoundation\Response;
use Yuzu\PipelineBundle\FeedbackForm\FeedbackFormProvider;

class PipelineController extends Controller
{
    public function subItemsAction($locationId)
    {
        $criteria = array(
            new Criterion\ParentLocationId($locationId),
            new Criterion\Visibility(Criterion\Visibility::VISIBLE),
            new Criterion\LogicalNot(
                new Criterion\ContentTypeIdentifier('pipeline_theme')
            ),
        );

        $query = new LocationQuery(
            array(
                'criterion' => new Criterion\LogicalAnd(
                    $criteria
                ),
                'sortClauses' => array(
                    new SortClause\Location\Priority(Query::SORT_ASC),
                ),
            )
        );

        $searchService = $this->getRepository()->getSearchService();
        $subContent = $searchService->findLocations($query);
        $treeItems = array();
        foreach ($subContent->searchHits as $hit) {
            $treeItems[] = $hit->valueObject;
        }

        return $this->get('ez_content')->viewLocation(
            $locationId,
            'full',
            true,
            array('items' => $treeItems)
        );
    }

    public function pipelineSettingsAction($template = null)
    {
        $root = $this->getRootLocation();
        $rootContent = $this->getRepository()->getContentService()->loadContent($root->contentInfo->id);

        // Get the current language
        $languages = $this->getConfigResolver()->getParameter('languages');
        $current_language = $languages[0];

        // Get top menu entries
        $topMenuContentIds = $rootContent->fields['top_menu'][$current_language]->destinationContentIds;
        $topMenuContentNamesArray = array();
        foreach ($topMenuContentIds as $topMenuContentId) {
            $topMenuContent = $this->getRepository()->getContentService()->loadContentInfo($topMenuContentId);
            $topMenuContentNamesArray[] = $topMenuContent;
        }

        return $this->render(
            $template,
            array(
                'content' => $rootContent,
                'top_menu_items' => $topMenuContentNamesArray,
            )
        );
    }

    public function portfolioItemsAction($locationId)
    {
        $criteria = array(
            new Criterion\ParentLocationId($locationId),
            new Criterion\Visibility(Criterion\Visibility::VISIBLE),
            new Criterion\ContentTypeIdentifier(array('image')),
        );

        $query = new Query(
            array(
                'criterion' => new Criterion\LogicalAnd(
                    $criteria
                ),
            )
        );

        $searchHits = $this->getRepository()->getSearchService()->findContent($query)->searchHits;

        $itemsArray = array();
        foreach ($searchHits as $searchHit) {
            $content = $searchHit->valueObject;
            $itemsArray[] = $content;
        }

        return $this->render(
            'YuzuPipelineBundle:portfolio:sub_items.html.twig',
            array(
                'items' => $itemsArray,
            )
        );
    }

    public function showFeedbackFormAction($locationId, $viewType, $layout = false, array $params = array())
    {
        $feedback = new Feedback();

        $form = $this->getFeedbackFormProvider()->getFeedbackForm($feedback);

        return $this->get('ez_content')->viewLocation(
            $locationId,
            $viewType,
            $layout,
            $params + array('form' => $form->createView(), 'onlyMarkup' => true, 'onlyJavascript' => false)
        );
    }

    public function feedbackFormAction()
    {
        $feedback = new Feedback();

        $form = $this->getFeedbackFormProvider()->getFeedbackForm($feedback);

        $request = $this->get('request');

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $message = \Swift_Message::newInstance();

                $message->setSubject('[Website] - Feedback Form has been submitted!')
                    ->setFrom($this->container->getParameter('mailer_user'))
                    ->setTo($request->request->get('recipient'))
                    ->setBody($this->renderView(
                        'YuzuPipelineBundle:mail:feedback_form.html.twig',
                        array(
                            'name' => $form->get('name')->getData(),
                            'email' => $form->get('email')->getData(),
                            'message' => $form->get('message')->getData(),
                        )
                    ));

                $this->get('mailer')->send($message);

                $return = json_encode(
                    array('messagesent' => true)
                );
            } else {
                $return = json_encode(
                    array('messagesent' => false)
                );
            }

            return new Response(
                $return,
                200,
                array('Content-Type' => 'application/json')
            );
        }
    }

    /**
     * @return FeedbackFormProvider
     */
    private function getFeedbackFormProvider()
    {
        return $this->get('yuzu_pipeline.feedback_form_provider');
    }
}
