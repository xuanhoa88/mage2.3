<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Plugin;


class ReaderPlugin
{
    /**
     * @var \CleverSoft\CleverLayeredNavigation\Model\Search\RequestGenerator
     */
    protected $requestGenerator;

    /**
     * ReaderPlugin constructor.
     *
     * @param \CleverSoft\CleverLayeredNavigation\Model\Search\RequestGenerator $requestGenerator
     */
    public function __construct(
        \CleverSoft\CleverLayeredNavigation\Model\Search\RequestGenerator $requestGenerator
    ) {
        $this->requestGenerator = $requestGenerator;
    }


    public function aroundRead(
        \Magento\Framework\Config\ReaderInterface $subject,
        \Closure $proceed,
        $scope = null
    ) {
        $result = $proceed($scope);
        $result = array_merge_recursive($result, $this->requestGenerator->generate());
        return $result;
    }
}
