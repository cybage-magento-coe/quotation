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

class Myquotation extends \Magento\Framework\View\Element\Template {

    protected $currentCustomer;
    protected $_quotation;
    protected $_priceBox;
    protected $_quotationHelper;



    public function __construct(
                \Magento\Framework\View\Element\Template\Context 
                $context, array $data = array(),
                 \Cybage\Quotation\Model\Quotation $Quotation,
                \Magento\Customer\Model\Session $currentCustomer,
                \Cybage\Quotation\Helper\Data $quotationHelper
            ) {
        $this->currentCustomer = $currentCustomer;
        $this->_quotation = $Quotation;
        $this->_quotationHelper = $quotationHelper;
        $this->getQuotations();
        parent::__construct($context, $data);
    }
    
    public function getQuotationStatus($status){
        return $this->_quotationHelper->getQuotationStatus($status);
    }

        public function getQuotations() {
        $currentCustomerId = $this->currentCustomer->getCustomerId();

        $quotationCollection = $this->_quotation->getCollection()
        ->addFieldToFilter('customer_id', $currentCustomerId);
        $data = $quotationCollection->getData();
        return $data;
    }

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getViewUrl($id=null) {
        return $this->getUrl('quotation/view/index/', ['id' => $id]);
    }

    public function getBackUrl() {
        return $this->getUrl('customer/account/index');
    }


}
