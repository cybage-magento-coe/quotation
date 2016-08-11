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

namespace Cybage\Quotation\Block;

class Quotationdetails extends \Magento\Framework\View\Element\Template {

    private $_quotation;
    private $_action;
    private $_quotationItem;
    protected $_resource;
    protected $_eavEntity;
    protected $_eavEntityAttribute;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context, array $data = array(), \Cybage\Quotation\Model\Quotation $quotation, \Cybage\Quotation\Model\ResourceModel\QuotationItem\Collection $quotationItem, \Magento\Framework\App\Action\Action $action, \Magento\Framework\App\ResourceConnection $resource, \Magento\Eav\Model\Entity $eavEntity, \Magento\Eav\Model\Entity\Attribute $eavEntityAttribute
    ) {
        $this->_quotation = $quotation;
        $this->_quotationItem = $quotationItem;
        $this->_action = $action;
        $this->_resource = $resource;
        $this->_eavEntity = $eavEntity;
        $this->_eavEntityAttribute = $eavEntityAttribute;
        parent::__construct($context, $data);
    }

    public function getQuotationDetails() {
        //$connection  = $this->_resource->getConnection();
        $productEntityTable = $this->_resource->getTableName('catalog_product_entity');
        $productEntityVarcharTable = $this->_resource->getTableName('catalog_product_entity_varchar');

        $entityTypeId = $this->_eavEntity
                ->setType('catalog_product')
                ->getTypeId();
        $prodNameAttrId = $this->_eavEntityAttribute
                ->loadByCode($entityTypeId, 'name')
                ->getAttributeId();
        /*$prodSimageAttrId = $this->_eavEntityAttribute
                ->loadByCode($entityTypeId, 'small_image')
                ->getAttributeId();*/
        $quotationId = $this->_action->getRequest()->getParam('id');
        $quotation = $this->_quotation
                ->getCollection()
                ->addFieldToFilter('main_table.id', $quotationId);
        $quotation->getSelect()
                ->join(array('item' => 'b2b_quotation_item'), 'item.quotation_id = main_table.id', array(
                    'id as item_id',
                    'product_id',
                    'qty', 'product_price',
                    'proposed_price',
                    'sku',
                    'options',
                    'parent_id'
        ));
        $quotation->getSelect()
                ->join(array(
                    'product' => $productEntityTable,
                        ), 'item.product_id=product.entity_id', array('sku'))
                ->join(
                        array('cpev' => $productEntityVarcharTable), 'cpev.entity_id=product.entity_id AND cpev.attribute_id=' . $prodNameAttrId, array('name' => 'value')
                )
                /*->join(
                        array('cpevI' => $productEntityVarcharTable), 'cpev.entity_id=product.entity_id AND cpev.attribute_id=' . $prodSimageAttrId, array('image' => 'value')
        )*/;
        return $quotation->getData();
    }

}
