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

class Updatequotationitem implements ObserverInterface {

    private $_quotation;
    private $_quotationItem;
    private $_quotationId;
    private $_product;
    private $_productOption;
    private $_productOptionValue;
    private $_itemLog;

    public function __construct(\Cybage\Quotation\Model\Quotation $quotation, \Cybage\Quotation\Model\ResourceModel\QuotationItem\Collection $quotationItem, \Magento\Catalog\Model\Product $product, \Magento\Catalog\Model\Product\Option $productOption, \Magento\Catalog\Model\Product\Option\Value $productOptionValue, \Cybage\Quotation\Model\QuotationItemLog $itemLog
    ) {
        $this->_quotation = $quotation;
        $this->_quotationItem = $quotationItem;
        $this->_product = $product;
        $this->_productOption = $productOption;
        $this->_productOptionValue = $productOptionValue;
        $this->_itemLog = $itemLog;
    }

    public function execute(Observer $observer) {
        $item = $observer->getItem();

        $quotationItemCollection = $this->_quotationItem->addFieldToFilter('quotation_id', $this->_quotationId);
        $productPrice = 0;
        try {
            $productDetails = $this->getProductDetails($item->getProductId());
            $qty = (float) $item->getQty();
            if ($item->getOptions() && $productDetails->getTypeID() == 'simple') {
                $options = unserialize($item->getOptions());
                $optionIds = array();
                $optionValues = array();
                foreach ($options as $key => $value) {
                    $optionIds[] = $key;
                    $optionValues[] = $value;
                }
                $opprice = $this->getOptionPrice($productDetails, $optionIds, $optionValues);
                $qty = $item->getQty();
                $productPrice = $opprice * $qty;
            } elseif ($productDetails->getTypeID() == 'configurable') {
                $options = unserialize($item->getOptions());

                $opprice = $this->getConfigurableProductPrice($productDetails, $options);
                $productPrice = $opprice * $qty;
            } elseif ($productDetails->getTypeID() == 'bundle') {
                $options = unserialize($item->getOptions());
                $opprice = $this->getBundleProductPrice($productDetails, $options);
                $productPrice = $opprice * $qty;
            } else {
                $opprice = $productDetails->getPrice();
                $productPrice = $opprice * $qty;
            }
            if (!$item->getSku()) {
                $item->setSku($productDetails->getSku());
            }
            $item->setProductPrice($productPrice);

            if ($item->getId()) {
                $this->_itemLog
                        ->setQuotationItemId($item->getId())
                        ->setQty($qty)
                        ->setProductPrice($productPrice)
                        ->setProductPrice($productPrice)
                        ->setProposedPrice($item->getProposedPrice())
                        ->save();
            }
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

    private function getConfigurableProductPrice($product = null, $options = null) {
        if ($product && $options) {
            $attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);
            $pricesByAttributeValues = array();
            $basePrice = $product->getFinalPrice();
            $simple = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($simple as $sProduct) {
                $totalPrice = $basePrice;
                $confArray = array();
                foreach ($attributes as $attribute) {
                    $attributeId = $attribute->getId();
                    $productAttribute = $attribute->getProductAttribute();
                    $productAttributeId = $productAttribute->getId();
                    $value = $sProduct->getData($attribute->getProductAttribute()->getAttributeCode());
                    $confArray[$productAttributeId] = $value;
                }
                if ($options == $confArray) {
                    return $sProduct->getPrice();
                }
            }
        }
    }

    private function getBundleProductPrice($product = null, $options = null) {
        $price = 0;
        try {
            $selections = $product->getTypeInstance(true)
                    ->getSelectionsCollection($product->getTypeInstance(true)
                    ->getOptionsIds($product), $product);

            foreach ($selections as $selection) {
                if ($options['bundle_option'][$selection->getOptionId()] == $selection->getSelectionId()) {
                    $price += $selection->getPrice() * $options['bundle_option_qty'][$selection->getOptionId()];
                }
            }

            return $price;
        } catch (Exception $exc) {
            echo $exc->getMessage();
        }
    }

}
