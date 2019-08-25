<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Model;


use CleverSoft\CleverLayeredNavigation\Api\Data\OptionSettingInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class OptionSetting
 * @method \CleverSoft\CleverLayeredNavigation\Model\ResourceModel\OptionSetting\Collection getCollection()
 * @package CleverSoft\CleverLayeredNavigation\Model
 */
class OptionSetting extends \Magento\Framework\Model\AbstractModel implements OptionSettingInterface, IdentityInterface
{
    const CACHE_TAG = 'clevershopby_option_setting';
    const IMAGES_DIR = '/cleversoft/shopby/option_images/';

    protected $_eventPrefix = 'clevershopby_option_setting';

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $storeManager;

    /** @var  Filesystem\Driver\File */
    protected $fileDriver;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        Filesystem $fileSystem,
        Filesystem\Driver\File $file,
        UploaderFactory $uploaderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->fileSystem = $fileSystem;
        $this->uploaderFactory = $uploaderFactory;
        $this->storeManager = $storeManager;
        $this->fileDriver = $file;
        parent::__construct(
            $context, $registry, $resource, $resourceCollection, $data
        );
    }


    protected function _construct()
    {
        $this->_init('CleverSoft\CleverLayeredNavigation\Model\ResourceModel\OptionSetting');
    }

    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    public function getMetaDescription()
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    public function getMetaKeywords()
    {
        return $this->getData(self::META_KEYWORDS);
    }

    public function getMetaTitle()
    {
        return $this->getData(self::META_TITLE);
    }

    public function getId()
    {
        return $this->getData(self::OPTION_SETTING_ID);
    }

    protected function getImage()
    {
        return $this->getData(self::IMAGE);
    }

    public function getFilterCode()
    {
        return $this->getData(self::FILTER_CODE);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    public function getTopCmsBlockId()
    {
        return $this->getData(self::TOP_CMS_BLOCK_ID);
    }

    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    public function setMetaDescription($metaDescription)
    {
        return $this->setData(self::META_DESCRIPTION, $metaDescription);
    }

    public function setMetaKeywords($metaKeywords)
    {
        return $this->setData(self::META_KEYWORDS, $metaKeywords);
    }

    public function setMetaTitle($metaTitle)
    {
        return $this->setData(self::META_TITLE, $metaTitle);
    }

    public function setId($id)
    {
        return $this->setData(self::OPTION_SETTING_ID, $id);
    }

    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }

    public function setFilterCode($filterCode)
    {
        return $this->setData(self::FILTER_CODE, $filterCode);
    }

    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }

    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    public function setTopCmsBlockId($id)
    {
        return $this->setData(self::TOP_CMS_BLOCK_ID, $id);
    }

    public function uploadImage($fileId)
    {
        $mediaDir = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setFilesDispersion(false);
        $uploader->setFilenamesCaseSensitivity(false);
        $uploader->setAllowRenameFiles(true);
        $uploader->setAllowedExtensions(['jpg', 'png', 'jpeg', 'gif', 'bmp']);
        $uploader->save($mediaDir->getAbsolutePath(self::IMAGES_DIR));
        $result = $uploader->getUploadedFileName();
        $this->removeImage();
        return $result;
    }

    public function removeImage()
    {
        if(!$this->getData('image_use_default') || $this->getStoreId() == 0) {
            if ($this->getImage()) {
                $path = $this->getImagePath();
                if ($this->fileDriver->isExists($path)) {
                    $this->fileDriver->deleteFile($path);
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getImagePath()
    {
        $mediaDir = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
        return $mediaDir->getAbsolutePath(self::IMAGES_DIR.$this->getImage());
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        if(!$this->getImage()){
            return null;
        }
        $url = $this->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . self::IMAGES_DIR . $this->getImage();

        return $url;
    }

    /**
     * @param $filterCode
     * @param $optionId
     * @param $storeId
     *
     * @return \CleverSoft\CleverLayeredNavigation\Model\OptionSetting
     */
    public function getByParams($filterCode, $optionId, $storeId)
    {
        $collection = $this->getCollection()->addLoadParams($filterCode, $optionId, $storeId);
        $model = $collection->getFirstItem();
        if($collection->count() > 1) {
            $defaultModel = $collection->getLastItem();
            foreach($model->getData() as $key=>$value) {
                if($defaultModel->getData($key) == $value){
                    $model->setData($key.'_use_default', true);
                }
            }
        } else {
            foreach($model->getData() as $key=>$value) {
                $model->setData($key.'_use_default', true);
            }
        }
        return $model;
    }

    /**
     * @param $filterCode
     * @param $optionId
     *
     * @return \CleverSoft\CleverLayeredNavigation\Model\ResourceModel\OptionSetting\Collection
     */
    public function getDependencyModels($filterCode, $optionId)
    {
        return $collection = $this
            ->getCollection()
            ->addFieldToFilter('filter_code', $filterCode)
            ->addFieldToFilter('value', $optionId)
            ->addFieldToFilter('store_id', ['neq'=>0]);
    }
}
