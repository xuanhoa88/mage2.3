<?php
/**
 * @category    CleverSoft
 * @package     CleverPageBuilder
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
namespace CleverSoft\CleverBuilder\Block\Adminhtml\User;

class Edit extends \Magento\User\Block\User\Edit {
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    protected $_customerFactory;

    protected $_userFactory;
    protected $storeManager;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = [],
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->_coreRegistry = $registry;
        $this->_userFactory = $userFactory;
        $this->storeManager = $context->getStoreManager();
        $this->_customerFactory = $customerFactory;
        parent::__construct($context,$registry, $data);
    }
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $userId = $this->getRequest()->getParam($this->_objectId);

        if (!empty($userId)) {
            ///load customer
            $websiteId  = $this->storeManager->getWebsite()->getWebsiteId();
            $user = $this->_userFactory->create()->load($userId);
            $userEmail = $user->getEmail();
            if($userEmail) {
                $customer = $this->_customerFactory->create()->setWebsiteId($websiteId)->loadByEmail($userEmail);
                if($customer->getId()){
                    $confirmMsg = __("Are you sure you want to deactive this account ?");
                    $this->addButton(
                        'panel',
                        [
                            'label' => __('Deactive CleverBuilder Account'),
                            'class' => 'save',
                            'onclick' => "deleteConfirm('" . $confirmMsg . "', '" . $this->getDeactiveBuilderAcountUrl() . "')",
                        ]
                    );
                } else {
                    $confirmMsg = __("Are you sure you want to active this account ?");
                    $this->addButton(
                        'panel',
                        [
                            'label' => __('Active CleverBuilder Account'),
                            'class' => 'save',
                            'onclick' => "deleteConfirm('" . $confirmMsg . "', '" . $this->getSaveBuilderAcountUrl() . "')",
                        ]
                    );
                }
            }


        }
    }

    public function getSaveBuilderAcountUrl(){
        return $this->getUrl('cleverbuilder/user/create', ['_current' => true]);
    }

    public function getDeactiveBuilderAcountUrl(){
        return $this->getUrl('cleverbuilder/user/deactivebuilder', ['_current' => true]);
    }
}