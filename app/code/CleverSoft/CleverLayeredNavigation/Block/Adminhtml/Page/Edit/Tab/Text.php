<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Block\Adminhtml\Page\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Category\Attribute\Source\Page as CategoryAttributeSourcePage;
use CleverSoft\CleverLayeredNavigation\Controller\RegistryConstants;

/**
 * Class Text
 *
 * @author Artem Brunevski
 */

class Text extends Generic implements TabInterface
{
    /**
     * @var CategoryAttributeSourcePage
     */
    protected $_categoryAttributeSourcePage;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param CategoryAttributeSourcePage $categoryAttributeSourcePage
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        CategoryAttributeSourcePage $categoryAttributeSourcePage,
        array $data = []
    ) {
        $this->_categoryAttributeSourcePage = $categoryAttributeSourcePage;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Page Text');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('cleversoft_shopby_');

        /** @var \CleverSoft\CleverLayeredNavigation\Model\Page $model */
        $model = $this->_coreRegistry->registry(RegistryConstants::PAGE);

        $fieldset = $form->addFieldset(
            'page_fieldset',
            ['legend' => __('Page Text'), 'class' => 'fieldset-wide']
        );

        if ($model->getId()) {
            $fieldset->addField('page_id', 'hidden', ['name' => 'page_id']);
        }

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'title' => __('Title')
            ]
        );

        $fieldset->addField(
            'description',
            'textarea',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description')
            ]
        );

        $fieldset->addField('top_block_id', 'select', array(
            'name'     => 'top_block_id',
            'label'    => __('Top CMS block'),
            'values' => $this->_categoryAttributeSourcePage->getAllOptions()
        ));

        /*
             $fieldset->addField('bottom_block_id', 'select', array(
                'name'     => 'bottom_block_id',
                'label'    => __('Bottom CMS block'),
                'values' => $this->_categoryAttributeSourcePage->getAllOptions()
            ));
        */

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }
}