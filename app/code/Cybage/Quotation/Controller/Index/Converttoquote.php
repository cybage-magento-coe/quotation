<?php

/**
 * Cybage Quotation Plugin
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is available on the World Wide Web at:
 * htt
  /**p://opensource.org/licenses/osl-3.0.php
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

namespace Cybage\Quotation\Controller\Index;

class Converttoquote extends \Magento\Customer\Controller\AbstractAccount {
    protected $_customer;
    protected $_customerId;
    protected $_checkoutSession;
    protected $_quoteItem;
    protected $_cart;
    protected $_productHelper;
    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customer, 
            \Magento\Checkout\Model\Session $checkoutSession, \Magento\Quote\Model\Quote\Item $quoteItem, 
            \Magento\Checkout\Model\Cart $cart,
            \Magento\Catalog\Helper\Product\Configuration $productHelper
    ) {
        $this->_customer = $customer;
        $this->_customerId = $this->_customer->getCustomerId();
        $this->_checkoutSession = $checkoutSession;
        $this->_quoteItem = $quoteItem;
        $this->_cart = $cart;
        $this->_productHelper = $productHelper;
        parent::__construct($context);
    }

    public function execute() {
        $this->deleteQuoteItems();
    }
    
    /**
     * Deletes existing cart items
     */
    private function deleteQuoteItems() {
        $checkoutSession = $this->_checkoutSession;
        $allItems = $checkoutSession->getQuote()->getAllVisibleItems();
        foreach ($allItems as $item) {
            $itemId = $item->getItemId(); //item id of particular item
            $quoteItem = $this->_quoteItem->load($itemId); //load particular item which you want to delete by his item id
//            $demo  = $item->getOptions();
//            echo get_class($item);
            //$this->_optionCollection->addItemFilter($item->getId());
            echo '<pre>';
//            var_dump($item->getOptions());
            print_r($this->_productHelper->getCustomOptions($item));
            echo '</pre>';
            //$quoteItem->delete(); //deletes the item
        }
    }

}
