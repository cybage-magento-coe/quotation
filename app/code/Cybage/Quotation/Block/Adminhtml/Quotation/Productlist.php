<?php
namespace Cybage\Quotation\Block\Adminhtml\Quotation;
class Productlist extends \Magento\Backend\Block\Widget{
    protected $_coreRegistry = null;
    public function __construct(\Magento\Backend\Block\Template\Context $context, array $data = array(),\Magento\Framework\Registry $registry) {
        $this->_coreRegistry = $registry;
        $this->setTemplate('Cybage_Quotation::products.phtml');
        parent::__construct($context, $data);
    }
    
    public function getList(){
        return $this->_coreRegistry->registry('quotation_details');
    }
}
