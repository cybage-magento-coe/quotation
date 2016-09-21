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

namespace Cybage\Quotation\Block;

class Addtocart extends \Magento\Framework\View\TemplateEngine\Php
{

    private $customer;
    private $_helper;
    private $_pricingHelper;
    private $_dateTime;
    public $_storeManager;
    private $_formKey;

    public function __construct(\Magento\Framework\ObjectManagerInterface $helperFactory, \Magento\Customer\Model\Session $session, \Magento\Framework\Pricing\Helper\Data $pricingHelper, \Magento\Framework\Stdlib\DateTime\DateTime $dateTime, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Data\Form\FormKey $formKey)
    {
        $this->customer = $session;
        $this->_pricingHelper = $pricingHelper;
        $this->_dateTime = $dateTime;
        $this->_storeManager = $storeManager;
        $this->_formKey = $formKey;
        parent::__construct($helperFactory);
    }

    /**
     * Show button check 
     * @param type $page
     * @return boolean
     */
    public function showAddToQuote($page = 'list')
    {
        ;
        $helper = $this->helper('\Cybage\Quotation\Helper\Data');
        switch ($page)
        {
            case 'list':
                if ($helper->isQuoteAllowedFromCatalog() && $this->customer->isLoggedIn()) {
                    return true;
                }
                break;
            case 'pdp':
                if ($this->customer->isLoggedIn()) {
                    return true;
                }
                break;
            case 'cart':
                //isQuoteAllowedFromCart
                if ($helper->isQuoteAllowedFromCart() && $this->customer->isLoggedIn()) {
                    return true;
                }
                break;
            default :
                return false;
                break;
        }
        return false
        ;
    }

    public function getAddToQuoteURL($product = null)
    {
        if ($product) {
            return $product->getUrlModel()->getUrl($product);
        }
    }

    /**
     * returns string in price format
     * @param type number
     * @return type string
     */
    public function priceFormat($price)
    {
        return $this->_pricingHelper->currency($price, true, false);
    }

    /**
     * returns formated date
     *
     */
    public function gmtDate($date = null, $format = null)
    {
        return $this->_dateTime->gmtDate($format, $date);
    }

    public function getFrmKey()
    {
        return $this->_formKey->getFormKey();
    }
}
