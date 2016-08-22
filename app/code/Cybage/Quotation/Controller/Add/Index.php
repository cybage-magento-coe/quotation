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

namespace Cybage\Quotation\Controller\Add;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Customer\Controller\AbstractAccount {

    protected $_quotation;
    protected $_customer;
    protected $_customerId;
    protected $_quotationItem;
    protected $_quotationId;
    protected $_formKeyValidator;
    protected $_product;
    protected $_configurableProduct;
    protected $_managerinterface;
    protected $_event;

    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Cybage\Quotation\Model\Quotation $quotation, 
            \Magento\Customer\Model\Session $customer, \Cybage\Quotation\Model\QuotationItem $quotationItem, 
            \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator, \Magento\Catalog\Model\Product $product, 
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProduct, 
            \Magento\Framework\Message\ManagerInterface $managerinterface, \Magento\Framework\Event\ManagerInterface $event
    ) {
        $this->_quotation = $quotation;
        $this->_customer = $customer;
        $this->_customerId = $this->_customer->getCustomerId();
        $this->_quotationItem = $quotationItem;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_product = $product;
        $this->_configurableProduct = $configurableProduct;
        $this->_managerinterface = $managerinterface;
        $this->_event = $event;
        parent::__construct($context);
    }

    public function execute() {
        $data = $this->getRequest()->getParams();
//        echo '<pre>';
//        print_r($data);
//        echo '</pre>';
//        die();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        // Your code

        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        if (!empty($data)) {
            try {
                $this->_quotationId = $this->activeQuotationId();
                if (!$this->_quotationId) {
                    $this->_quotationId = $this->createQuotation();
                }
                if ($this->_quotationId) {
                    $this->addQuotationItem();
                }
                $this->_managerinterface->addSuccess('Product successfully added to quotation');
                $this->_event->dispatch('btob_quotation_item_update_after', array('id' => $this->_quotationId));
            } catch (Exception $exc) {
                $this->_managerinterface->addError($exc->getMessage());
            }
            $resultRedirect->setPath('quotation/view/index', array('id' => $this->_quotationId));
            return $resultRedirect;
        } else {
            $resultRedirect->setPath('customer/account/');
            return $resultRedirect;
        }
    }

    /**
     * Check for active quotation for this user and return the ID
     * @return int
     */
    private function activeQuotationId() {
        $collection = $this->_quotation->getCollection()
                ->addFieldToFilter('customer_id', $this->_customerId)
                ->addFieldToFilter('quotation_status', 7);

        if ($collection->getFirstItem()->getId()) {
            return $collection->getFirstItem()->getId();
        }
        return false;
    }

    /**
     * Creates new Quotation and returns ID
     */
    private function createQuotation() {
        $this->_quotation->setCustomerId($this->_customerId);
        $this->_quotation->setQuotationStatus(7);
        $this->_quotation->save();
        return $this->_quotation->getId();
    }

    /**
     * Add Item to Quotation 
     */
    private function addQuotationItem() {
        $data = $this->getRequest()->getParams();
//        echo '<pre>';
//        print_r($data);


        if ($data['product']) {
            try {
                /* Check for Simple Product */
                $item = $this->getQuotationItemId($data);
                $param = array();
                if (!empty($item)) {
                    if (isset($data['qty'])) {
                        $param['qty'] = (int) $item['qty'] + $data['qty'];
                    } else {
                        $param['qty'] = 1;
                    }
                    $param['id'] = $item['id'];
                } else {
                    $param['qty'] = (int) isset($data['qty']) ? $data['qty'] : 0;
                    $param['productid'] = $data['product'];
                    if (isset($data['options'])) {
                        $param['options'] = serialize($data['options']);
                    } elseif (isset($data['super_attribute'])) {
                        $param['options'] = serialize($data['super_attribute']);
                    } elseif (isset($data['bundle_option']) && isset($data['bundle_option_qty'])) {
                        $options = array();
                        $options['bundle_option'] = $data['bundle_option'];
                        $options['bundle_option_qty'] = $data['bundle_option_qty'];
                        $param['options'] = serialize($options);
                    }
                }
                $parentId = $this->saveQuotationItem($param);

                /* check for grouped product */
                if (isset($data['super_group']) && !empty($data['super_group'])) {
                    foreach ($data['super_group'] as $key => $value) {
                        $data['product'] = $key;
                        $item = $this->getQuotationItemId($data);
                        $param = array();
                        if (!empty($item)) {
                            $param['qty'] = (int) $item['qty'] + $value;
                            $param['id'] = $item['id'];
                        } else {
                            $param['productid'] = $key;
                            $param['qty'] = $value;
                        }
                        $this->saveQuotationItem($param);
                    }
                }
                /* check for grouped product end */
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }
    }

    /**
     * insert/update records to quotation_item Table
     */
    private function saveQuotationItem($param = null) {
        $id = '';
        if ($param) {
            try {
                if (isset($param['id'])) {
                    $item = $this->_quotationItem->load($param['id']);
                    $item->setQty($param['qty']);
                    $this->_event->dispatch('btob_quotation_item_update_before', array('item' => $item));
                    $item->save();
                } else {
                    $this->_quotationItem->setQuotationId($this->_quotationId);
                    if (isset($param['productid'])) {
                        $this->_quotationItem->setProductId($param['productid']);
                    }
                    if (isset($param['qty'])) {
                        $this->_quotationItem->setQty($param['qty']);
                    }
                    if (isset($param['parentid'])) {
                        $this->_quotationItem->setParentId($param['parentid']);
                    }
                    if (isset($param['options'])) {
                        $this->_quotationItem->setOptions($param['options']);
                    }
                    $this->_event->dispatch('btob_quotation_item_update_before', array('item' => $this->_quotationItem));

                    if ($param['qty']) {
                        $this->_quotationItem->save();
                        $id = $this->_quotationItem->getId();
                    }
                }
                $this->_quotationItem->unsetData();
            } catch (Exception $exc) {
                echo $exc->getMessage();
            }
        }
        return $id;
    }

    /**
     * check whether Item is already added to Quotation if yes then returns details
     * @param type $pid
     */
    private function getQuotationItemId($data = null, $parentId = false, $childId = false) {
        if (!empty($data)) {
            try {
                $collection = $this->_quotationItem->getCollection()
                        ->addFieldToFilter('quotation_id', $this->_quotationId);
                if ($parentId && $childId) {

                    $collection->addFieldToFilter('parent_id', $parentId);
                    $collection->addFieldToFilter('product_id', $childId);
                } else {
                    $collection = $this->_quotationItem->getCollection()
                            ->addFieldToFilter('product_id', $data['product']);
                }
                if (isset($data['options'])) {
                    $collection->addFieldToFilter('options', serialize($data['options']));
                } elseif (isset($data['super_attribute'])) {
                    $collection->addFieldToFilter('options', serialize($data['super_attribute']));
                } elseif (isset($data['bundle_option']) && isset($data['bundle_option_qty'])) {
                    $options = array();
                    foreach ($data['bundle_option'] as $key => $value) {
                        $options[$value] = $data['bundle_option_qty'][$key];
                    }
                    $collection->addFieldToFilter('options', serialize($options));
                }
            } catch (Exception $exc) {
                echo $exc->getMessage();
            }
            return $collection->getFirstItem()->getData();
        }
    }

    /**
     * returns the array of child product ids
     * @param type $data
     */
    private function getSelectedChildProducts($data = null) {
        $child;
        $i = 1;
        try {
            if (!empty($data) && !isset($data['super_attribute'])) {
                $slectionArray = array();
                $product = $this->_product->load($data['product']);
                $selections = $product->getTypeInstance(true)
                        ->getSelectionsCollection($product->getTypeInstance(true)
                        ->getOptionsIds($product), $product);

                foreach ($selections as $selection) {
                    $slectionArray[$i++] = $selection->getId();
                }
                $i = 1;
                if (!empty($slectionArray)) {
                    foreach ($data['bundle_option'] as $key => $value) {
                        $child[$i++] = $slectionArray[$value];
                    }
                }
            } elseif (isset($data['super_attribute'])) {
                $product = $this->_product->load($data['product']);
                $child = $this->_configurableProduct
                        ->getProductByAttributes($data['super_attribute'], $product);
            }
        } catch (Exception $exc) {
            echo $exc->getMessage();
        }
        return $child;
    }

}
