<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverBuilder\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


class InstallData implements InstallDataInterface
{

    /**
     * Authorization factory
     *
     * @var AuthorizationFactory
     */
    protected $_customerSetup;
    /**
     * @var AttributeSetFactory
     */

    protected $_eavSetup;
    private $_attributeSetFactory;
    /**
     * Init
     *
     * @param AuthorizationFactory $authFactory
     */
    public function __construct(CustomerSetupFactory $customerSetup, EavSetupFactory $eavSetup , AttributeSetFactory $attributeSetFactory) {
        $this->_customerSetup = $customerSetup;
        $this->_eavSetup = $eavSetup;
        $this->_attributeSetFactory = $attributeSetFactory;
    }
    /*
     *
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $customerSetup = $this->_customerSetup->create(['setup' => $setup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet = $this->_attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(Customer::ENTITY, 'account_is_a_builder', [
            'type' => 'int',
            'label' => 'Active Frontend Builder Account ?',
            'input' => 'select',
            'required' => false,
            'visible' => true,
            'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'sort_order' => 991,
            'position' => 991,
            'default' => 0,
            'system' => 0,
        ]);

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'account_is_a_builder')
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer'],
            ]);

        $attribute->save();
    }
}