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

class Complete implements ObserverInterface
{

    private $_customer;
    private $_quotation;

    public function __construct(
    \Cybage\Quotation\Model\Quotation $quotation, \Magento\Customer\Model\Session $customer
    )
    {
        $this->_quotation = $quotation;
        $this->_customer = $customer;
    }

    public function execute(Observer $observer)
    {
        $orderInstance = $observer->getOrder();
        $incrementId = $orderInstance->getIncrementId();
        $quotatioinId = $this->_customer->getQuotationId();

        if ($quotatioinId) {
            try {
                $quotation = $this->_quotation->load($quotatioinId);
                $quotation->setOrderId($incrementId);
                $quotation->setQuotationStatus(6);
                $quotation->save();
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }
    }

}
