<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Plugin\Ajax;


class LayeredNavigationViewAjax extends \CleverSoft\CleverLayeredNavigation\Plugin\Ajax\Ajax
{

    /**
     * @param \CleverSoft\CleverLayeredNavigation\Controller\Index\Index $controller
     * @param                                           $page
     *
     * @return \Magento\Framework\Controller\Result\Raw|\Magento\Framework\View\Result\Page
     */
    public function afterExecute(\CleverSoft\CleverLayeredNavigation\Controller\Index\Index $controller,  $page)
    {
        if(!$this->isAjax($controller) || !$controller->getRequest()->getParam('CLN') || !$page instanceof \Magento\Framework\View\Result\Page )
        {
            return $page;
        }

        $responseData = $this->getAjaxResponseData($page);
        $response = $this->prepareResponse($responseData);
        return $response;

    }
}
