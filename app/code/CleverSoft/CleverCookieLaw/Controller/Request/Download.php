<?php 
namespace CleverSoft\CleverCookieLaw\Controller\Request;

class Download extends \Magento\Framework\App\Action\Action
{
    protected $fileFactory;
    protected $csvProcessor;
    protected $directoryList;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \CleverSoft\CleverCookieLaw\File\Json $jsonProcessor,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    )
    {
        $this->_customerFactory = $customerFactory;
        $this->fileFactory = $fileFactory;
        $this->jsonProcessor = $jsonProcessor;
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        $this->_storeManager = $storeManager;
        $this->_objectManager = $objectmanager;
        parent::__construct($context);
    }

    public function execute()
    {
        $request = $this->getRequest()->getParams();
        if ($request) {
            $fileName = $request['email'].".".$request['type'];
            $filePath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)
                . "/" . $fileName;

            $websiteId = $this->_storeManager->getStore()->getWebsiteId();
            $customer = $this->_customerFactory->create()->setWebsiteId($websiteId)->loadByEmail($request['email']);
            $personalData = $this->getPresonalData($customer, $request['type']);

            if ($request['type'] == 'csv') {
                $this->csvProcessor
                ->setDelimiter(';')
                ->setEnclosure('"')
                ->saveData(
                    $filePath,
                    $personalData
                );
            } else {
                $this->jsonProcessor
                ->setDelimiter(';')
                ->setEnclosure('"')
                ->saveData(
                    $filePath,
                    $personalData
                );
            }

            return $this->fileFactory->create(
                $fileName,
                [
                    'type' => "filename",
                    'value' => $fileName,
                    'rm' => true,
                ],
                \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
                'application/octet-stream'
            );
        }
    }

    protected function getPresonalData($customer, $type)
    {
        $quote = $this->_objectManager->create('Magento\Quote\Model\Quote')->loadByCustomer($customer->getId()); 
        $quoteItems = $quote->getAllVisibleItems();
        
        $cartData = [];
        foreach($quoteItems as $key => $item){
            $cartData[$key] = ["name" => $item->getName(), "sku" => $item->getSku(), "price" => $this->formatPrice($item->getPrice())];
        }
        $cartData["subtotal"] = $this->formatPrice($quote->getSubtotal());
        $result = [];

        if ($type == 'csv') {
            $customer->setData("cart",json_encode($cartData));
            $result[] = $customer->getData();
            return $result;
        }  else {
            $customer->setData("cart",$cartData);
            $result[] = $customer->getData();
            return json_encode($result);
        }
    }

    public function formatPrice($price) {
        $currency = $this->_objectManager->get('Magento\Framework\Pricing\Helper\Data');
        return $currency->currency($price, true, false);
    }
}