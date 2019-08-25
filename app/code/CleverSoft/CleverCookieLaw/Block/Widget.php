<?php
/**
 * @category    CleverSoft
 * @package     CleverCookieLaw
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverCookieLaw\Block;
use Magento\Framework\View\Element\Template;

class Widget extends Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_template = 'private-policy.phtml';

    public function __construct(
        Template\Context $context,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->_sessionFactory = $sessionFactory;
    }

    public function isLogged() {
    	return $this->_sessionFactory->create()->isLoggedIn();
    }

    public function getCustomerEmail() {
    	return $this->_sessionFactory->create()->getCustomer()->getEmail();
    }
}