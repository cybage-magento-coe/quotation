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

class Updatequotation implements ObserverInterface
{

    private $_quotation;
    private $_quotationItem;
    private $_quotationLog;
    private $_request;
    private $_customerCollection;
    private $_quotationHelper;

    public function __construct(
    \Cybage\Quotation\Model\Quotation $quotation, \Cybage\Quotation\Model\QuotationItem $quotationItem, \Cybage\Quotation\Model\QuotationLog $quotationLog, \Magento\Framework\App\RequestInterface $request, \Magento\Customer\Model\ResourceModel\Customer\Collection $customerCollection, \Cybage\Quotation\Helper\Data $quotationHelper
    )
    {
        $this->_quotation = $quotation;
        $this->_quotationItem = $quotationItem;
        $this->_quotationLog = $quotationLog;
        $this->_request = $request;
        $this->_customerCollection = $customerCollection;
        $this->_quotationHelper = $quotationHelper;
    }

    public function execute(Observer $observer)
    {
        try {
            $data = $this->_request->getParams();
            $quotationId = $observer->getId();
            $totalProductPrice = 0;
            $totalProposedPrice = 0;
            $this->_quotationItem->unsetData();
            $collection = $this->_quotationItem->getCollection()
                    ->addFieldToFilter('quotation_id', ['eq' => $quotationId]);
            $quotation = $this->_quotation->load($quotationId);
            if ($collection->count()) {
                foreach ($collection as $value) {
                    $totalProductPrice += $value->getProductPrice();
                    $totalProposedPrice += $value->getProposedPrice();
                }

                $quotation->setTotalProductPrice($totalProductPrice);
                $quotation->setTotalProposedPrice($totalProposedPrice);
                if (isset($data['submit_quotation']) && $data['submit_quotation'] == 'submit') {
                    $quotation->setQuotationStatus(($quotation->getQuotationStatus() == 7) ? 0 : 4);
                    $this->sendEmail($quotation);
                }
                if (isset($data['delivery_date'])) {
                    $quotation->setDeliveryDate($data['delivery_date']);
                }
                $quotation->save();

                $this->_quotationLog
                        ->setQuotationId($quotationId)
                        ->setTotalProductPrice($totalProductPrice)
                        ->setTotalProposedPrice($totalProposedPrice)
                        ->setDeliveryDate($this->_quotation->getDeliveryDate())
                        ->save();
            } else {
                $quotation->delete();
            }
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    public function sendEmail($quotation)
    {
        $customerId = $quotation->getCustomerId();
        $customer = $this->_customerCollection->addAttributeToFilter('entity_id', $customerId)->getFirstItem();


        /* Receiver Detail  */
        $receiverInfo = [
            'name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
            'email' => $customer->getEmail(),
        ];


        /* Sender Detail  */
        $senderInfo = [
            'name' => 'Admin',
            'email' => 'admin@website.com',
        ];


        /* Assign values for your template variables  */
        //$statusArray = $this->_quotationHelper->getQuotationStatus($status);
        $emailTemplateVariables = [];
        $emailTempVariables['customer_name'] = $customer->getFirstname() . ' ' . $customer->getLastname();
        $emailTempVariables['quotation_status'] = $this->_quotationHelper->getQuotationStatus($quotation->getQuotationStatus());

        /* We write send mail function in helper because if we want to 
          use same in other action then we can call it directly from helper */

        /* call send mail method from helper or where you define it */
        $this->_quotationHelper->sendMail(
                $emailTempVariables, $senderInfo, $receiverInfo, true
        );
    }

}
