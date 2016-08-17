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

    public function __construct(
    \Cybage\Quotation\Model\Quotation $quotation, \Cybage\Quotation\Model\QuotationItem $quotationItem
    ) {
        $this->_quotation = $quotation;
        $this->_quotationItem = $quotationItem;
    }

    public function execute(Observer $observer) {
        try {
            echo $quotationId = $observer->getId();
            $totalProductPrice = 0;
            $totalProposedPrice = 0;
            $this->_quotationItem->unsetData();
            $collection = $this->_quotationItem->getCollection()
                    ->addFieldToFilter('quotation_id', array('eq'=>$quotationId));
//            echo $collection->getSelect();
//            echo '<pre>';
//            print_r($collection->getData());
//            echo '</pre>';
            foreach ($collection as $value) {
                $totalProductPrice += $value->getProductPrice();
                $totalProposedPrice += $value->getProposedPrice();
            }
            $this->_quotation->load($quotationId)
                    ->setTotalProductPrice($totalProductPrice)
                    ->setTotalProposedPrice($totalProposedPrice)
                    ->save();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

}
