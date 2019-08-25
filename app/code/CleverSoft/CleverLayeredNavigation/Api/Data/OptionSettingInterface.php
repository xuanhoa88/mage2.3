<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Api\Data;

interface OptionSettingInterface
{
    const DESCRIPTION = 'description';
    const FILTER_CODE = 'filter_code';
    const IMAGE = 'image';
    const META_DESCRIPTION = 'meta_description';
    const META_KEYWORDS = 'meta_keywords';
    const META_TITLE = 'meta_title';
    const OPTION_SETTING_ID = 'option_setting_id';
    const VALUE = 'value';
    const TITLE = 'title';
    const TOP_CMS_BLOCK_ID = 'top_cms_block_id';

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return string
     */
    public function getFilterCode();

    /**
     * @return string|null
     */
    public function getImageUrl();

    /**
     * @return string
     */
    public function getMetaDescription();

    /**
     * @return string
     */
    public function getMetaKeywords();

    /**
     * @return string
     */
    public function getMetaTitle();

    /**
     * @return int
     */
    public function getValue();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return int|null
     */
    public function getTopCmsBlockId();

    /**
     * @param string $description
     * @return OptionSettingInterface
     */
    public function setDescription($description);

    /**
     * @param string $filterCode
     * @return OptionSettingInterface
     */
    public function setFilterCode($filterCode);

    /**
     * @param int $id
     * @return OptionSettingInterface
     */
    public function setId($id);

    /**
     * @param int $value
     * @return OptionSettingInterface
     */
    public function setValue($value);

    /**
     * @param string $metaDescription
     * @return OptionSettingInterface
     */
    public function setMetaDescription($metaDescription);

    /**
     * @param string $metaKeywords
     * @return OptionSettingInterface
     */
    public function setMetaKeywords($metaKeywords);

    /**
     * @param string $metaTitle
     * @return OptionSettingInterface
     */
    public function setMetaTitle($metaTitle);

    /**
     * @param string $title
     * @return OptionSettingInterface
     */
    public function setTitle($title);

    /**
     * @param int|null $id
     * @return OptionSettingInterface
     */
    public function setTopCmsBlockId($id);
}
