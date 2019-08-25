<?php
/**
 * @category    CleverSoft
 * @package     CleverPageBuilder
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
namespace CleverSoft\CleverBuilder\Helper\Customer;

use Magento\Customer\Model\Customer;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Model\StoreManagerInterface;

class Reindex extends \Magento\Framework\App\Helper\AbstractHelper {
    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(\Magento\Framework\App\Helper\Context $context, IndexerRegistry $indexerRegistry) {
        $this->indexerRegistry = $indexerRegistry;
        parent::__construct($context);
    }

    public function reindexAll() {
        $indexer = $this->indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
        $indexer->reindexAll();
    }

}