<?php
/**
 * @category    CleverSoft
 * @package     CleverBuilder
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
namespace CleverSoft\CleverBuilder\Helper\Ajax;

/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
class AjaxUpdateDbPanelsData extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_modelPanel;
    protected $_panelTemplate;
    protected $_panelHelper;
    protected $_storeManager;
    protected $_dataHelper;
    protected $_pageFactory;
    /**
     * @param Context $context
     */
    public function __construct(\Magento\Framework\App\Helper\Context $context , \Magento\Framework\ObjectManagerInterface $objectmanager, \CleverSoft\CleverBuilder\Helper\Panels\Panels $panels ,\CleverSoft\CleverBuilder\Model\PanelsData $panelsData , \CleverSoft\CleverBuilder\Model\PanelsTemplate $panelTemplate, \Magento\Store\Model\StoreManagerInterface $storeManagerInterface , \Magento\Cms\Model\PageFactory $pageFactory, \CleverSoft\CleverBuilder\Helper\Elements\AbstractElement $abstractElement, \CleverSoft\CleverBuilder\Model\Cssgen\Generator $generator){
        parent::__construct($context);
        $this->_objectManager = $objectmanager;
        $this->_modelPanel = $panelsData;
        $this->_panelTemplate = $panelTemplate;
        $this->_panelHelper = $panels;
        $this->_dataHelper = $panels->getDataHelper();
        $this->_storeManager = $storeManagerInterface;
        $this->_pageFactory = $pageFactory;
        $this->_abstractElement = $abstractElement;
        $this->_cssGenerator = $generator;
    }
    /*
     * save panels data
     */
    public function savePanelsData($post) {
        $data = array(
            'page_id' => $post['page_id'],
            'setting' => $post['panels_data']
        );
        $exist = $this->_dataHelper->getPanelsData($post['page_id'],true);

        if($exist) {
            $data['id'] = $exist;
        }
        try{
//            foreach ($this->_dataHelper->getPageStoreIds($post['page_id']) as $sID) {
                // we don't need store_id field in the database, it was removed because the changes will be applied to all stores which the page is assigned to.
//                $data['store_id'] = $sID;
            //save panel data
                $this->_modelPanel->addData($data);
                $this->_modelPanel->save();
//                unset($data['store_id']);
//            }
            $this->savePanelsHtmlPage($post);
            $this->_dataHelper->setCustomerStatusPanel(0);
            echo '';
            die();
        }catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }
    /*
     * update panels data
     */
    public function updatePanelsData($post) {
        $data = array(
            'page_id' => $post['page_id'],
            'setting' => $post['panels_data']
        );
        $exist = $this->_dataHelper->getPanelsData($post['page_id'],true);

        if($exist) {
            $data['id'] = $exist;
        }
        try{
            $this->_modelPanel->addData($data);
            $this->_modelPanel->save();
            $this->savePanelsHtmlPage($post);
            echo '';
            die();
        }catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }
    /*
     * update prebuilt panels data
     */
    public function prebuiltPanelsData($storeId, $post) {
        $layout = $post['layout'];
        if ($post['template']) {
            $model = $this->_objectManager->create("CleverSoft\CleverBuilder\Model\PanelsTemplate")->load($layout);
            if ($model->getId()) {
                $setting = $model->getSetting();
            } else {
                $settings = '';
            }
        } else {
            $jsonFiles = $this->_abstractElement->getPrebuiltLayoutJsonFile();
            $setting = file_get_contents($this->_abstractElement->getAbsolutePatchModule().'/Elements/PrebuiltLayout/'.$jsonFiles[$layout]);

        }
        $data = array(
            'page_id' => $post['page_id'],
            'setting' => $setting
        );

        $exist = $this->_dataHelper->getPanelsData($post['page_id'],true);

        if($exist) {
            $data['id'] = $exist;
        }
        try{
            $this->_modelPanel->addData($data);
            $this->_modelPanel->save();
            $this->_cssGenerator->generateCss($storeId, $setting, $post['page_id']);
            echo '';
            die();
        }catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }
    /*
     * save template
     */
    public function saveTemplate($post) {
        $data = array(
            'title' => $post['title'],
            'setting' => $post['panels_data']
        );
        try{
            $this->_panelTemplate->addData($data);
            $this->_panelTemplate->save();
            echo '';
            die();
        }catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }
    /*
     * remove template
     */
    public function removeTemplate($post) {
        $layout = $post['layout'];
        if ($post['template']) {
            $model = $this->_objectManager->create("CleverSoft\CleverBuilder\Model\PanelsTemplate")->load($layout);
            if ($model->getId()) {
                try{
                    $model->delete();
                    echo '';
                    die();
                }catch (\Exception $e) {
                    $this->_logger->critical($e);
                }
            } else {
                return;
            }
        } else {
            return;
        }
    }
    /*
     * save html into cms_page.
     */
    protected function savePanelsHtmlPage($post) {
        if ( empty( $post['page_id'] ) || empty( $post['panels_data'] ) ) {
            return ;
        }
        $panels_data            = json_decode( $post['panels_data'] , true );
        $panels_data            = $this->_panelHelper->sanitize_all( $panels_data );
        $panels_html =  $this->_panelHelper->renderer()->render( intval( $post['page_id'] ), false, $panels_data );
        $storeIDs = $this->_dataHelper->getPageStoreIds($post['page_id']);
        $data = array(
            'page_id' => $post['page_id'],
            'content' => $panels_html
        );
        try{
            $this->_pageFactory->create()
                ->load($post['page_id'])
                ->addData($data)
                ->setStores($storeIDs)
                ->save();
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }

    }
}

