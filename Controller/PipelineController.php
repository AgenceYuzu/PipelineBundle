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
use Symfony\Component\HttpFoundation\Response;
use Yuzu\PipelineBundle\Entity\Feedback;

class PipelineController extends Controller
{
    /**
     * @param int $locationId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

    /**
     * @param string $template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function pipelineSettingsAction($template = null)
    {
        $contentService = $this->getRepository()->getContentService();

        $root = $this->getRootLocation();
        $rootContent = $contentService->loadContent($root->contentInfo->id);

        // Get the current language
        $languages = $this->getConfigResolver()->getParameter('languages');
        $currentLanguage = $languages[0];

        // Get top menu entries
        $topMenuContentIds = $rootContent->fields['top_menu'][$currentLanguage]->destinationContentIds;
        $topMenuContentNamesArray = array();
        foreach ($topMenuContentIds as $topMenuContentId) {
            $topMenuContent = $contentService->loadContentInfo($topMenuContentId);
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

    /**
     * @param int $locationId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

    /**
     * @param int $locationId
     * @param string $viewType
     * @param bool|false $layout
     * @param array $params
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

    /**
     * @param \Symfony\Component\HttpFoundation\Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function feedbackFormAction(Request $request)
    {
        $feedback = new Feedback();

        $form = $this->getFeedbackFormProvider()->getFeedbackForm($feedback);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            $return = json_encode(
                array('messagesent' => false)
            );

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
            }

            return new Response(
                $return,
                200,
                array('Content-Type' => 'application/json')
            );
        }
    }

    /**
     * @return \Yuzu\PipelineBundle\FeedbackForm\FeedbackFormProvider
     */
    private function getFeedbackFormProvider()
    {
        return $this->get('yuzu_pipeline.feedback_form_provider');
    }
}
