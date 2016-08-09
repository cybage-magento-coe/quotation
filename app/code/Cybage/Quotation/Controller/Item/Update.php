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

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator, \Cybage\Quotation\Model\QuotationItem $quotationitem, \Magento\Framework\Message\ManagerInterface $managerinterface, \Magento\Framework\Event\ManagerInterface $event, \Cybage\Quotation\Helper\Data $data) {

        $this->_formKeyValidator = $formKeyValidator;
        $this->_quotationitem = $quotationitem;
        $this->_managerinterface = $managerinterface;
        $this->_event = $event;
        $this->_quotaionhelper = $data;
        parent::__construct($context);
    }

    public function execute() {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $data = $this->getRequest()->getParams();
        $setData = array();
        $quotationId = null;
        if (!empty($data)) {
            try {
                foreach ($data as $key => $value) {
                    
                    $temp = explode('_', $key);
                    $id = end($temp);
                    array_reverse($temp);
                    array_pop($temp);
                    $setData['id'] = $id;
                    if(!$quotationId){
                        $param['item_id'] = $id;
                        $quotationId = $this->_quotaionhelper->getQuotationId($param['item_id']);
                    }
                    $setData[implode('_', $temp)] = $value;
                    $this->_quotationitem->setData($setData);
                    $this->_quotationitem->save();
                    $this->_quotationitem->unsetData();
                }
                echo $quotationId;
                $this->_event->dispatch('btob_quotation_updated', array('quotationid' => 123));
                $this->_managerinterface->addSuccess('Quotation successfully updated');
            } catch (Exception $exc) {
                $this->_managerinterface->addError($exc->getMessage());
            }
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

}
