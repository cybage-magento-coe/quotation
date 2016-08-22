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

namespace Cybage\Quotation\Controller\Item;

use Magento\Framework\Controller\ResultFactory;

class Update extends \Magento\Customer\Controller\AbstractAccount {

//put your code here
    protected $_formKeyValidator;
    protected $_quotationitem;
    protected $_managerinterface;
    protected $_event;
    protected $_quotaionhelper;
    protected $_quotationComment;
    protected $_customer;
    protected $_customerId;
    protected $_checkoutSession;
    protected $_quoteItem;
    protected $_cart;
    protected $_product;

    public function __construct(
    \Magento\Framework\App\Action\Context $context, 
            \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator, 
            \Cybage\Quotation\Model\QuotationItem $quotationitem, 
            \Magento\Framework\Message\ManagerInterface $managerinterface, 
            \Magento\Framework\Event\ManagerInterface $event, 
            \Cybage\Quotation\Helper\Data $data, 
            \Cybage\Quotation\Model\QuotationComment $quotationComment, 
            \Magento\Customer\Model\Session $customer, 
            \Magento\Checkout\Model\Session $checkoutSession, 
            \Magento\Quote\Model\Quote\Item $quoteItem, 
            \Magento\Checkout\Model\Cart $cart, 
            \Magento\Catalog\Api\ProductRepositoryInterface $product
    ) {

        $this->_formKeyValidator = $formKeyValidator;
        $this->_quotationitem = $quotationitem;
        $this->_managerinterface = $managerinterface;
        $this->_event = $event;
        $this->_quotaionhelper = $data;
        $this->_quotationComment = $quotationComment;
        $this->_customer = $customer;
        $this->_customerId = $this->_customer->getCustomerId();
        $this->_checkoutSession = $checkoutSession;
        $this->_quoteItem = $quoteItem;
        $this->_cart = $cart;
        $this->_product = $product;
        parent::__construct($context);
    }

    public function execute() {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $data = $this->getRequest()->getParams();
        $setData = array();
//        echo '<pre>';
//        print_r($data);
//        echo '</pre>';
//        die();
        if (!empty($data) && !isset($data['convert_to_cart'])) {
            try {
                foreach ($data as $key => $value) {
                    $temp = explode('_', $key);
                    $id = end($temp);
                    if (!is_numeric($id)) {
                        continue;
                    }
                    array_reverse($temp);
                    array_pop($temp);
                    $func = 'set' . $this->dashesToCamelCase(implode('_', $temp));
                    $this->_quotationitem->load($id);
                    $this->_quotationitem->$func($value);
                    $this->_event->dispatch('btob_quotation_item_update_before', array('item' => $this->_quotationitem));
                    $this->_quotationitem->save();
                    $this->_quotationitem->unsetData();
                }
                $this->_event->dispatch('btob_quotation_item_update_after', array('id' => $data['quotationid']));
                if (isset($data['comment']) && !empty($data['comment'])) {
                    $this->_quotationComment->setQuotationId($data['quotationid'])
                            ->setCustomerId($this->_customerId)
                            ->setComment($data['comment'])
                            ->setCommenttedBy('c')
                            ->save();
                }
                $this->_managerinterface->addSuccess('Quotation successfully updated');
            } catch (Exception $exc) {
                $this->_managerinterface->addError($exc->getMessage());
            }
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        } else {
            $this->deleteQuoteItems();
            if ($this->addItemsToCart($data['quotationid'])) {
                $this->_customer->setQuotationId($data['quotationid']);
                $resultRedirect->setUrl('/checkout');
                return $resultRedirect;
            } else {
                $this->_managerinterface->addError('Some unexpected error happend.');
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;
            }
        }
    }

    /**
     * converts a string to camelcase
     * @param type $string
     * @param type $capitalizeFirstCharacter
     * @return type
     */
    private function dashesToCamelCase($string, $capitalizeFirstCharacter = false) {
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));

        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }
        return $str;
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
            $quoteItem->delete(); //deletes the item
        }
    }

    private function addItemsToCart($quotationId = null) {
        if ($quotationId) {
            $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
            try {
                $collection = $this->_quotationitem->getCollection()
                        ->addFieldToFilter('quotation_id', $quotationId);
                foreach ($collection as $value) {
                    $product = $this->_product->getById($value->getProductId(), false, $storeId);
                    $param = array();
                    $param['product'] = $value->getProductId();
                    $options = unserialize($value->getOptions());
                    if (isset($options['bundle_option']) && isset($options['bundle_option_qty'])) {
                        $param['bundle_option'] = $options['bundle_option'];
                        $param['bundle_option_qty'] = $options['bundle_option_qty'];
                    } elseif ($product->getTypeId() == 'configurable') {
                        $param['super_attribute'] = $options;
                    } else {
                        $param['options'] = $options;
                    }
                    $param['qty'] = $value->getQty();
                    $this->_cart->addProduct($product, $param);
                    $this->_cart->save();
                }
                return true;
            } catch (Exception $exc) {
                return false;
                //echo $exc->getTraceAsString();
            }
        }
    }

}
