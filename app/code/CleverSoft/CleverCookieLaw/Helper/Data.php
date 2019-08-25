<?php
/**
 * @category    CleverSoft
 * @package     CleverCookieLaw
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverCookieLaw\Helper;

use Magento\Framework\Filesystem;

/**
 * Captcha image model
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const DELETE_EMAIL_TEMPLATE = 'cookielaw/general/delete_email_template';
    const RECTIFY_EMAIL_TEMPLATE = 'cookielaw/general/rectify_email_template';
    const COMPLAINT_EMAIL_TEMPLATE = 'cookielaw/general/complaint_email_template';
    const DATA_EMAIL_TEMPLATE = 'cookielaw/general/data_email_template';
    const CONFIRM_EMAIL_TEMPLATE = 'cookielaw/general/confirm_email_template';
    const CANCEL_EMAIL_TEMPLATE = 'cookielaw/general/cancel_email_template';
    const ADMIN_EMAIL_ID = 'trans_email/ident_sales/email';
    const ADMIN_STORE_NAME = 'trans_email/ident_sales/name';

    protected $helper;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;
    
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Cms\Helper\Page $helper,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function isActive()
    {
        return !$this->scopeConfig->getValue('advanced/modules_disable_output/Valdecode_CookieLaw');
    }

    public function getCmsPage()
    {
        $pageId = $this->scopeConfig->getValue('cookielaw/content/cms_page');
        return $this->helper->getPageUrl($pageId);
    }

    public function getSystemConfig($group, $field)
    {
        return $this->scopeConfig->getValue('cookielaw/' . $group . '/' . $field, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getStoreEmail()
    {
        return $this->scopeConfig->getValue(self::ADMIN_EMAIL_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getStorename()
    {
        return $this->scopeConfig->getValue(self::ADMIN_STORE_NAME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getDefaultTransEmailId()
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_general/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendDeleteMail($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->_template = $this->getTemplateId(self::DELETE_EMAIL_TEMPLATE);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();
        $this->_inlineTranslation->resume();
    }

    /**
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendRectifyMail($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->_template = $this->getTemplateId(self::RECTIFY_EMAIL_TEMPLATE);

        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();
        $this->_inlineTranslation->resume();
    }

    /**
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendComplaintMail($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->_template = $this->getTemplateId(self::COMPLAINT_EMAIL_TEMPLATE);

        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();
        $this->_inlineTranslation->resume();
    }

    /**
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendDataMail($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->_template = $this->getTemplateId(self::DATA_EMAIL_TEMPLATE);

        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();
        $this->_inlineTranslation->resume();
    }

    /**
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendConfirmMail($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->_template = $this->getTemplateId(self::CONFIRM_EMAIL_TEMPLATE);

        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();
        $this->_inlineTranslation->resume();
    }

    /**
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendCancelMail($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->_template = $this->getTemplateId(self::CANCEL_EMAIL_TEMPLATE);

        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();
        $this->_inlineTranslation->resume();
    }

    /**
     * Return template id.
     *
     * @return mixed
     */
    public function getTemplateId($xmlPath)
    {
        return $this->scopeConfig->getValue($xmlPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * [generateTemplate description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $template = $this->_transportBuilder->setTemplateIdentifier($this->_template)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'], $receiverInfo['name']);

        return $this;
    }
}
