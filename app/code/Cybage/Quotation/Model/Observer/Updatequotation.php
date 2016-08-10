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

namespace Cybage\Quotation\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Updatequotation implements ObserverInterface {

    private $_quotation;
    private $_quotationItem;
    private $_quotationId;
    private $_product;
    private $_productOption;
//    private $_totalProposedPrice;
//    private $_totalProductPrice;
    private $_productOptionValue;

    public function __construct(\Cybage\Quotation\Model\Quotation $quotation,
            \Cybage\Quotation\Model\ResourceModel\QuotationItem\Collection $quotationItem, 
            \Magento\Catalog\Model\Product $product, 
            \Magento\Catalog\Model\Product\Option $productOption, 
            \Magento\Catalog\Model\Product\Option\Value $productOptionValue
    ) {
        $this->_quotation = $quotation;
        $this->_quotationItem = $quotationItem;
        $this->_product = $product;
        $this->_productOption = $productOption;
        $this->_productOptionValue = $productOptionValue;
    }

    public function execute(Observer $observer) {
       $item = $observer->getItem();
        
        $quotationItemCollection = $this->_quotationItem->addFieldToFilter('quotation_id', $this->_quotationId);
        $productPrice = 0;
        try {
            $productDetails = $this->getProductDetails($item->getProductId());
            $qty = (float) $item->getQty();
            if ($item->getOptions() && $productDetails->getTypeID() == 'simple') {
                $oprions = unserialize($item->getOptions());
                $optionIds = array();
                $optionValues = array();
                foreach ($oprions as $key => $value) {
                    $optionIds[] = $key;
                    $optionValues[] = $value;
                }
                $opprice = $this->getOptionPrice($productDetails, $optionIds, $optionValues);
                $qty = $item->getQty();
                $productPrice = $opprice * $qty;
            } else {
                $opprice = $productDetails->getPrice();
                $productPrice = $opprice * $qty;
            }
            if (!$item->getSku()) {
                $item->setSku($productDetails->getSku());
            }
            $item->setProductPrice($productPrice);
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    /**
     * rerturns the product object
     * @param type $pid
     * @return type object
     */
    private function getProductDetails($pid) {
        return $this->_product->load($pid);
    }

    /**
     * Update quotation table
     */
//    private function updateQuotation() {
//        $this->_quotation->load($this->_quotationId)
//                ->setTotalProductPrice($this->_totalProductPrice)
//                ->setTotalProposedPrice($this->_totalProposedPrice)
//                ->save();
//    }

    public function getOptionPrice($product, $optionIds, $optionValues) {
        $options = $this->_productOption->getProductOptionCollection($product);
        $options->addFieldToFilter('main_table.option_id', array('in' => $optionIds));
        $productPrice = $product->getPrice();
        $optionPrice = 0;
        foreach ($options as $option) {
            $optionvalues = $this->_productOptionValue->getValuesCollection($option);
            $optionvalues->addFieldToFilter('main_table.option_type_id', array('in' => $optionValues));
            foreach ($optionvalues as $value) {
                $priceData = $value->getData();
                switch ($priceData['price_type']) {
                    case 'percent':
                        $optionPrice += ($productPrice * $priceData['price'] / 100);
                        break;
                    case 'fixed':
                        $optionPrice += $priceData['price'];
                        break;
                    case 'default':
                        $optionPrice = 0;
                        break;
                }
            }
        }
        return $productPrice + $optionPrice;
    }
    
    
    //the configurable product id
//$productId = 126; 
////load the product - this may not be needed if you get the product from a collection with the prices loaded.
//$product = Mage::getModel('catalog/product')->load($productId); 
////get all configurable attributes
//$attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);
////array to keep the price differences for each attribute value
//$pricesByAttributeValues = array();
////base price of the configurable product 
//$basePrice = $product->getFinalPrice();
////loop through the attributes and get the price adjustments specified in the configurable product admin page
//foreach ($attributes as $attribute){
//    $prices = $attribute->getPrices();
//    foreach ($prices as $price){
//        if ($price['is_percent']){ //if the price is specified in percents
//            $pricesByAttributeValues[$price['value_index']] = (float)$price['pricing_value'] * $basePrice / 100;
//        }
//        else { //if the price is absolute value
//            $pricesByAttributeValues[$price['value_index']] = (float)$price['pricing_value'];
//        }
//    }
//}
//
////get all simple products
//$simple = $product->getTypeInstance()->getUsedProducts();
////loop through the products
//foreach ($simple as $sProduct){
//    $totalPrice = $basePrice;
//    //loop through the configurable attributes
//    foreach ($attributes as $attribute){
//        //get the value for a specific attribute for a simple product
//        $value = $sProduct->getData($attribute->getProductAttribute()->getAttributeCode());
//        //add the price adjustment to the total price of the simple product
//        if (isset($pricesByAttributeValues[$value])){
//            $totalPrice += $pricesByAttributeValues[$value];
//        }
//    }

}
