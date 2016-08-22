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
class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    const QUOT_ENABLE = 'quotation_configuration/general/enable'; // QUOTATION ENABLE
    const QUOT_CAT_PAGE = 'quotation_configuration/general/quotation_from_catalog_page'; //QUOTATION FROM CATALOG PAGE 
    const QUOT_SER_RES = 'quotation_configuration/general/quotation_from_search_result'; // QUOTEATION FROM SEARCH RESULT PAGE
    const QUOT_SPNG_CRT = 'quotation_configuration/general/quotation_from_shopping_cart'; // QUOTATION FROM SHOPPING CART
    const QUOTE_LT = 'quotation_configuration/general/quotation_lifetime'; //QUOTATION LIFETIME IN DAYS
    const DEFAULT_LIFETINE = 7; // DEFAULT QUOTATION LIFETIME
    const STATUS_REQ = '0';
    const STATUS_APP = '1';
    const STATUS_RESP = '2';
    const STATUS_REJ = '3';
    const STATUS_REREQ = '4';
    const STATUS_ACC = '5';
    const STATUS_COM = '6';
    const STATUS_INT = '7';

    private $_scopeConfig;
    private $_quotationItem;
    public function __construct(Context $context, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Cybage\Quotation\Model\QuotationItem $quotationitem ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_quotationItem = $quotationitem;
        parent::__construct($context);
    }

    public function getQuotationStatusArray() {
        return array(
            self::STATUS_REQ => 'Requested',
            self::STATUS_APP => 'Approved',
            self::STATUS_RESP => 'Responded',
            self::STATUS_REJ => 'Rejected',
            self::STATUS_REREQ => 'Re-requested',
            self::STATUS_ACC => 'Accepted',
            self::STATUS_COM => 'Complete',
            self::STATUS_INT => 'Intermediate',
        );
    }

    public function getQuotationStatus($status) {
        $array = $this->getQuotationStatusArray();

        return $array[$status];
    }

    /**
     * Check if module is enabled
     * @param type $scopeType
     * @param type $scopeCode
     * @return type bool
     */
    public function isActive() {
        return $this->_scopeConfig->getValue(self::QUOT_ENABLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check if Quotation allowed from Catalog Page
     * @param type $scopeType
     * @param type $scopeCode
     * @return type bool
     */
    public function isQuoteAllowedFromCatalog() {
        return $this->_scopeConfig->getValue(self::QUOT_CAT_PAGE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        //return $this->scopeConfig(self::QUOT_CAT_PAGE, $scopeType, $scopeCode);
    }

    /**
     * Check if Quotation allowed from search result page
     * @param type $scopeType
     * @param type $scopeCode
     * @return type bool
     */
    public function isQuoteAllowedFromSearchResult($scopeType = Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null) {
        return $this->scopeConfig(self::QUOT_SER_RES, $scopeType, $scopeCode);
    }

    /**
     * Check if Quotation allowd from cart page
     * @param type $scopeType
     * @param type $scopeCode
     * @return type bool
     */
    public function isQuoteAllowedFromCart() {
        return $this->_scopeConfig->getValue(self::QUOT_SPNG_CRT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
//        return $this->scopeConfig(self::QUOT_SPNG_CRT, $scopeType, $scopeCode);
    }

    /**
     * Return quotation life time in days
     * @param type $scopeType
     * @param type $scopeCode
     * @return type int
     */
    public function getQuotationLifeTime($scopeType = Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null) {
        if ($this->scopeConfig(self::QUOTE_LT, $scopeType, $scopeCode) > 0) {
            return $this->scopeConfig(self::QUOTE_LT, $scopeType, $scopeCode);
        } else {
            return $this->scopeConfig(self::DEFAULT_LIFETINE, $scopeType, $scopeCode);
        }
    }
    
    /**
     * returns the quotation Id based on params
     * @param type $param
     */
    
    public function getQuotationId($param = array()){
        if(isset($param['item_id'])){
            return $this->_quotationItem->load((int)$param['item_id'])->getQuotationId();
        }
        
        return;
    }

}
