<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
 
namespace CleverSoft\CleverLayeredNavigation\Model\Request;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\RequestInterface;

class Builder extends \Magento\Framework\Search\Request\Builder
{
    /** @var \Magento\Framework\App\Request\Http */
    protected $httpRequest;

    protected $removablePlaceholders = [];

    public function __construct(
        ObjectManagerInterface $objectManager,
        \Magento\Framework\Search\Request\Config $config,
        \Magento\Framework\Search\Request\Binder $binder,
        \Magento\Framework\Search\Request\Cleaner $cleaner,
        \Magento\Framework\App\Request\Http $http
    )
    {
        parent::__construct($objectManager, $config, $binder, $cleaner);
        $this->httpRequest = $http;
    }

    public function autoSetRequestName()
    {
        $module = $this->httpRequest->getControllerModule();
        switch ($module) {
            case 'Magento_CatalogSearch':
                $requestName = 'quick_search_container';
                break;
            default:
                $requestName = 'catalog_view_container';
        }
        $this->setRequestName($requestName);
    }

    public function bind($placeholder, $value)
    {
        $this->removablePlaceholders[$placeholder] = $value;
        return $this;
    }

    public function removePlaceholder($placeholder)
    {
        if (array_key_exists($placeholder, $this->removablePlaceholders)) {
            unset($this->removablePlaceholders[$placeholder]);
        }
        return $this;
    }

    public function hasPlaceholder($placeholder)
    {
        return array_key_exists($placeholder, $this->removablePlaceholders);
    }

    /**
     * Create request object
     *
     * @return RequestInterface
     */
    public function create()
    {
        $this->commitCancelablePlaceholders();
        return parent::create();
    }

    protected function commitCancelablePlaceholders()
    {
        foreach ($this->removablePlaceholders as $key => $value) {
            parent::bind($key, $value);
        }
    }
}
