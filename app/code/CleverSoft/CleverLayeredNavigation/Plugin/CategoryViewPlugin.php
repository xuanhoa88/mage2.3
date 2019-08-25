<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */


namespace CleverSoft\CleverLayeredNavigation\Plugin;

/**
 * Class CategoryViewPlugin
 *
 * @author Artem Brunevski
 */

use Magento\Catalog\Block\Category\View;
use Magento\Catalog\Model\Category;
use CleverSoft\CleverLayeredNavigation\Helper\Meta;
use Magento\Framework\View\Result\Page;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultInterface;
use CleverSoft\CleverLayeredNavigation\Model\Page as ModalPage;
use CleverSoft\CleverLayeredNavigation\Model\Customizer\CategoryFactory as CustomizerCategoryFactory;

class CategoryViewPlugin
{
    /** @var  Meta */
    protected $metaHelper;

    /** @var CustomizerCategoryFactory  */
    protected $_customizerCategoryFactory;

    /** @var bool */
    protected $_categoryModified = false;

    /**
     * @param CustomizerCategoryFactory $customizerCategoryFactory
     */
    public function __construct(
        CustomizerCategoryFactory $customizerCategoryFactory,
        Meta $metaHelper
    ){
        $this->metaHelper = $metaHelper;
        $this->_customizerCategoryFactory = $customizerCategoryFactory;

    }

    /**
     * @param Action $subject
     * @param Page $result
     * @return ResultInterface
     */
    public function afterExecute(Action $subject, $result)
    {
        if ($result instanceof Page) {
            $this->metaHelper->setPageTags($result->getConfig());
        }
        return $result;
    }

    /**
     * @param View $subject
     * @param $isMixedMode
     * @return bool
     */
    public function afterIsMixedMode(View $subject, $isMixedMode)
    {
        if (!$isMixedMode) {
            $category = $subject->getCurrentCategory();
            if ($category->getData(ModalPage::CATEGORY_FORCE_MIXED_MODE)) {
                $isMixedMode = true;
            }

            if ($category->getData('clevershopby_force_mixed_mode')) {
                $isMixedMode = true;
            }
        }
        return $isMixedMode;
    }

    /**
     * @param View $subject
     * @param Category $category
     * @return Category
     */
    public function afterGetCurrentCategory(View $subject, $category)
    {
        if ($category instanceof Category && !$this->_categoryModified) {
            $this->_customizerCategoryFactory->create()
                ->prepareData($category);

            $this->_categoryModified = true;
        }
        return $category;
    }
}