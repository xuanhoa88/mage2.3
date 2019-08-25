<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Api\Data;

interface FilterSettingInterface
{
    const FILTER_SETTING_ID = 'setting_id';
    const FILTER_CODE = 'filter_code';
    const DISPLAY_MODE = 'display_mode';
    const IS_MULTISELECT = 'is_multiselect';
    const IS_SEO_SIGNIFICANT = 'is_seo_significant';
    const INDEX_MODE = 'index_mode';
    const FOLLOW_MODE = 'follow_mode';
    const HIDE_ONE_OPTION = 'hide_one_option';
    const IS_EXPANDED = 'is_expanded';
    const SORT_OPTIONS_BY = 'sort_options_by';
    const SHOW_PRODUCT_QUANTITIES = 'show_product_quantities';
    const IS_SHOW_SEARCH_BOX = 'is_show_search_box';
    const NUMBER_UNFOLDED_OPTIONS = 'number_unfolded_options';
    const TOOLTIP = 'tooltip';
    const IS_USE_AND_LOGIC = 'is_use_and_logic';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return int|null
     */
    public function getDisplayMode();

    /**
     * @return int
     */
    public function getFollowMode();

    /**
     * @return string|null
     */
    public function getFilterCode();

    /**
     * @return int
     */
    public function getHideOneOption();

    /**
     * @return int
     */
    public function getIndexMode();

    /**
     * @return bool|null
     */
    public function isMultiselect();

    /**
     * @return bool|null
     */
    public function isSeoSignificant();

    /**
     * @return bool|null
     */
    public function isExpanded();

    /**
     * @return int
     */
    public function getSortOptionsBy();

    /**
     * @return int
     */
    public function getShowProductQuantities();

    /**
     * @return bool
     */
    public function isShowSearchBox();

    /**
     * @return mixed
     */
    public function getNumberUnfoldedOptions();

    /**
     * @return bool
     */
    public function isUseAndLogic();

    /**
     * @return string
     */
    public function getTooltip();

    /**
     * @param int $id
     * @return FilterSettingInterface
     */
    public function setId($id);

    /**
     * @param int $displayMode
     * @return FilterSettingInterface
     */
    public function setDisplayMode($displayMode);

    /**
     * @param int $indexMode
     * @return FilterSettingInterface
     */
    public function setIndexMode($indexMode);

    /**
     * @param int $followMode
     * @return FilterSettingInterface
     */
    public function setFollowMode($followMode);

    /**
     * @param int $hideOneOption
     * @return FilterSettingInterface
     */
    public function setHideOneOption($hideOneOption);

    /**
     * @param bool $isMultiselect
     * @return FilterSettingInterface
     */
    public function setIsMultiselect($isMultiselect);

    /**
     * @param bool $isSeoSignificant
     * @return FilterSettingInterface
     */
    public function setIsSeoSignificant($isSeoSignificant);

    /**
     * @param bool $isExpanded
     *
     * @return FilterSettingInterface
     */
    public function setIsExpanded($isExpanded);

    /**
     * @param string $filterCode
     * @return FilterSettingInterface
     */
    public function setFilterCode($filterCode);

    /**
     * @param int $sortOptionsBy
     * @return FilterSettingInterface
     */
    public function setSortOptionsBy($sortOptionsBy);

    /**
     * @param int $showProductQuantities
     * @return FilterSettingInterface
     */
    public function setShowProductQuantities($showProductQuantities);

    /**
     * @param bool $isShowSearchBox
     * @return FilterSettingInterface
     */
    public function setIsShowSearchBox($isShowSearchBox);

    /**
     * @param int $numberOfUnfoldedOptions
     * @return FilterSettingInterface
     */
    public function setNumberUnfoldedOptions($numberOfUnfoldedOptions);

    /**
     * @param string $tooltip
     *
     * @return FilterSettingInterface
     */
    public function setTooltip($tooltip);

    /**
     * @param bool $isUseAndLogic
     *
     * @return FilterSettingInterface
     */
    public function setIsUseAndLogic($isUseAndLogic);
}
