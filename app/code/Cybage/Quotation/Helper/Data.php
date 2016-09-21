<?php
/**
 * Cybage Quotation Plugin
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is available on the World Wide Web at:
 * http://opensource.org/licenses/osl-3.0.php
 * If you are unable to access it on the World Wide Web, please send an email
 * To: Support_Magento@cybage.com.  We will send you a copy of the source file.
 *
 * @category   Quotation Plugin
 * @package    Cybage_Quotation
 * @copyright  Copyright (c) 2014 Cybage Software Pvt. Ltd., India
 *             http://www.cybage.com/pages/centers-of-excellence/ecommerce/ecommerce.aspx
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Cybage Software Pvt. Ltd. <Support_Magento@cybage.com>
 */
namespace Cybage\Quotation\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config;

/**
 * Class Data
 * @package Cybage\Quotation\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const QUOT_ENABLE = 'quotation_configuration/general/enable'; // QUOTATION ENABLE
    const QUOT_CAT_PAGE = 'quotation_configuration/general/quotation_from_catalog_page'; //QUOTATION FROM CATALOG PAGE 
    const QUOT_SER_RES = 'quotation_configuration/general/quotation_from_search_result'; // QUOTEATION FROM SEARCH RESULT PAGE
    const QUOT_SPNG_CRT = 'quotation_configuration/general/quotation_from_shopping_cart'; // QUOTATION FROM SHOPPING CART
    const QUOTE_LT = 'quotation_configuration/general/quotation_lifetime'; //QUOTATION LIFETIME IN DAYS
    const EMAIL_TEMPLATE_CUSTOMER = 'quotation_configuration/general/quotation_email_customer'; //CUSTOMER EMAIL TEMPLATE
    const EMAIL_TEMPLATE_ADMIN = 'quotation_configuration/general/quotation_email_admin'; //ADMIN EMAIL TEMPLATE
    const ADMIN_NAME = 'quotation_configuration/general/quotation_name'; //ADMIN NAME
    const ADMIN_EMAIL = 'quotation_configuration/general/quotation_admin_email'; //ADMIN EMAIL
    const DEFAULT_LIFETINE = 7; // DEFAULT QUOTATION LIFETIME
    const STATUS_REQ = '0';
    const STATUS_APP = '1';
    const STATUS_RESP = '2';
    const STATUS_REJ = '3';
    const STATUS_REREQ = '4';
    const STATUS_ACC = '5';
    const STATUS_COM = '6';
    const STATUS_INT = '7';
    const STATUS_EXP = '8';
    private $_scopeConfig;
    private $_quotationItem;
    const XML_PATH_EMAIL_TEMPLATE_FIELD = 'section/group/your_email_template_field_id';
    /* Here section and group refer to name of section and group where you create this field in configuration */
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    //protected $_scopeConfig;
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;
    /**
     * @var string
     */
    protected $temp_id;
    protected $_productImage;
    protected $_product;


    public function __construct(Context $context, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Cybage\Quotation\Model\QuotationItem $quotationitem, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation, \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder, \Magento\Catalog\Helper\Image $productImage, \Magento\Catalog\Model\Product $product)
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_quotationItem = $quotationitem;
        $this->_storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_productImage = $productImage;
        $this->_product = $product;
        parent::__construct($context);
    }
    public function getQuotationStatusArray()
    {
        return [
            self::STATUS_REQ => 'Requested',
            self::STATUS_APP => 'Approved',
            self::STATUS_RESP => 'Responded',
            self::STATUS_REJ => 'Rejected',
            self::STATUS_REREQ => 'Re-requested',
            self::STATUS_ACC => 'Accepted',
            self::STATUS_COM => 'Complete',
            self::STATUS_INT => 'Intermediate',
            self::STATUS_EXP => 'Expired',
        ];
    }
    public function getQuotationStatus($status)
    {
        $array = $this->getQuotationStatusArray();
        return $array[$status];
    }
    /**
     * Check if module is enabled
     * @param type $scopeType
     * @param type $scopeCode
     * @return type bool
     */
    public function isActive()
    {
        return $this->_scopeConfig->getValue(self::QUOT_ENABLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    /**
     * Check if Quotation allowed from Catalog Page
     * @param type $scopeType
     * @param type $scopeCode
     * @return type bool
     */
    public function isQuoteAllowedFromCatalog()
    {
        return $this->_scopeConfig->getValue(self::QUOT_CAT_PAGE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    /**
     * Check if Quotation allowed from search result page
     * @param type $scopeType
     * @param type $scopeCode
     * @return type bool
     */
    public function isQuoteAllowedFromSearchResult($scopeType = Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        return $this->scopeConfig(self::QUOT_SER_RES, $scopeType, $scopeCode);
    }
    /**
     * Check if Quotation allowd from cart page
     * @param type $scopeType
     * @param type $scopeCode
     * @return type bool
     */
    public function isQuoteAllowedFromCart()
    {
        return $this->_scopeConfig->getValue(self::QUOT_SPNG_CRT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    /**
     * Return quotation life time in days
     * @param type $scopeType
     * @param type $scopeCode
     * @return type int
     */
    public function getQuotationLifeTime()
    {
        return $this->_scopeConfig->getValue(self::QUOTE_LT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    /**
     * Return customer email template
     * @param type $scopeType
     * @param type $scopeCode
     * @return type int
     */
    public function getEmailTemplateCustomer()
    {
        return $this->_scopeConfig->getValue(self::EMAIL_TEMPLATE_CUSTOMER, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    /**
     * Return admin email template
     * @param type $scopeType
     * @param type $scopeCode
     * @return type int
     */
    public function getEmailTemplateAdmin()
    {
        return $this->_scopeConfig->getValue(self::ADMIN_EMAIL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    /**
     * Return admin email id
     * @param type $scopeType
     * @param type $scopeCode
     * @return type int
     */
    public function getAdminEmail()
    {
        return $this->_scopeConfig->getValue(self::EMAIL_TEMPLATE_ADMIN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    /**
     * Return admin email id
     * @param type $scopeType
     * @param type $scopeCode
     * @return type int
     */
    public function getAdminName()
    {
        return $this->_scopeConfig->getValue(self::ADMIN_NAME, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    /**
     * returns the quotation Id based on params
     * @param type $param
     */
    public function getQuotationId($param = [])
    {
        if (isset($param['item_id'])) {
            return $this->_quotationItem->load((int) $param['item_id'])->getQuotationId();
        }
        return;
    }
    /**
     * [sendInvoicedOrderEmail description]                  
     * @param  Mixed $emailTemplateVariables 
     * @param  Mixed $senderInfo             
     * @param  Mixed $receiverInfo           
     * @return void
     */
    /* your send mail method */
    public function sendMail($emailTemplateVariables, $senderInfo, $receiverInfo, $sendToCustomer = true)
    {
        try {
            if ($sendToCustomer) {
                $this->temp_id = $this->getEmailTemplateCustomer();
            } else {
                $this->temp_id = $this->getEmailTemplateAdmin();
            }
            $this->inlineTranslation->suspend();
            $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }
    /**
     * Return store configuration value of your template field that which id you set for template
     *
     * @param string $path
     * @param int $storeId
     * @return mixed
     */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
                        $path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
        );
    }
    /**
     * Return store 
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }
    /**
     * Return template id according to store
     *
     * @return mixed
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }
    /**
     * [generateTemplate description]  with template file and tempaltes variables values                
     * @param  Mixed $emailTemplateVariables 
     * @param  Mixed $senderInfo             
     * @param  Mixed $receiverInfo           
     * @return void
     */
    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $template = $this->_transportBuilder->setTemplateIdentifier($this->temp_id)
                ->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND, /* here you can defile area and
                              store of template for which you prepare it */
                            'store' => $this->_storeManager->getStore()->getId(),
                        ]
                )
                ->setTemplateVars($emailTemplateVariables)
                ->setFrom($senderInfo)
                ->addTo($receiverInfo['email'], $receiverInfo['name']);
        return $this;
    }
    
    public function getProductImaege($pid = null){
        if($pid){
            return $this->_productImage->init($this->_product->load($pid),'category_page_list')
                    ->constrainOnly(false)->keepAspectRatio(TRUE)->keepFrame(false)->resize(400)->getUrl();
        }
    }
}
