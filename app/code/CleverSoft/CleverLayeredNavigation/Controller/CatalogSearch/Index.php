<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CleverSoft\CleverLayeredNavigation\Controller\CatalogSearch;

use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\QueryFactory;

class Index extends \Magento\CatalogSearch\Controller\Result\Index
{
    /**
     * Catalog session
     *
     * @var Session
     */
    protected $_catalogSession;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var QueryFactory
     */
    private $_queryFactory;

    /**
     * Catalog Layer Resolver
     *
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @param Context $context
     * @param Session $catalogSession
     * @param StoreManagerInterface $storeManager
     * @param QueryFactory $queryFactory
     * @param Resolver $layerResolver
     */
    public function __construct(
        Context $context,
        Session $catalogSession,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        Resolver $layerResolver
    ) {
        parent::__construct($context,$catalogSession,$storeManager,$queryFactory,$layerResolver);
        $this->_storeManager = $storeManager;
        $this->_catalogSession = $catalogSession;
        $this->_queryFactory = $queryFactory;
        $this->layerResolver = $layerResolver;
    }
    /*
     *
     */
    public function execute()
    {
        $pluginAjax = $this->_objectManager->get('CleverSoft\CleverLayeredNavigation\Plugin\Ajax\Ajax');
        if(!$pluginAjax->isAjax($this) || !$this->getRequest()->getParam('CLN')) {
            parent::execute();
        } else {
            $this->layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);
            /* @var $query \Magento\Search\Model\Query */
            $query = $this->_queryFactory->get();

            $query->setStoreId($this->_storeManager->getStore()->getId());

            if ($query->getQueryText() != '') {
                if ($this->_objectManager->get(\Magento\CatalogSearch\Helper\Data::class)->isMinQueryLength()) {
                    $query->setId(0)->setIsActive(1)->setIsProcessed(1);
                } else {
                    $query->saveIncrementalPopularity();
                }
                $this->_objectManager->get(\Magento\CatalogSearch\Helper\Data::class)->checkNotes();
            }
            $page = $pluginAjax->resultPageFactory->create();
            $responseData = $pluginAjax->getAjaxSearchResponseData($page);
            $response = $pluginAjax->prepareResponse($responseData);
            return $response;
        }

    }
}
