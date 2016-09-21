<?php

namespace Cybage\Quotation\Block\Adminhtml\Quotation;

class Commentlist extends \Magento\Backend\Block\Widget
{

    protected $_request;
    protected $_comnentCollection;

    public function __construct(\Magento\Backend\Block\Template\Context $context, array $data = array(), \Magento\Framework\App\RequestInterface $request, \Cybage\Quotation\Model\ResourceModel\QuotationComment\Collection $comnentCollection)
    {
        $this->_request = $request;
        $this->_comnentCollection = $comnentCollection;
        $this->setTemplate('Cybage_Quotation::comments.phtml');
        parent::__construct($context, $data);
    }

    public function getList()
    {
        $id = $this->_request->getParam('id');
        return $collection = $this->_comnentCollection->addFieldToFilter('quotation_id', $id);
    }
}
