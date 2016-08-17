<?php

namespace Cybage\Quotation\Controller\Adminhtml\View;

use Magento\Backend\App\Action;

class Index extends \Magento\Backend\App\Action {

    
    protected $_coreRegistry = null;
    protected $resultPageFactory;
    protected $_quotationCollection;
    protected $_resource;

    public function __construct(
        Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\Registry $registry,
         \Cybage\Quotation\Model\ResourceModel\Quotation\Collection $quotation,
            \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_quotationCollection = $quotation;
        $this->_resource = $resource;
        parent::__construct($context);
    }

    

    
    protected function _initAction() {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Cybage_Quotation::post')
                ->addBreadcrumb(__('Quotation'), __('Quotation'))
                ->addBreadcrumb(__('Manage Quotation'), __('Manage Quotation'));
        return $resultPage;
    }

    
    public function execute() {
        
        $id = $this->getRequest()->getParam('id');
        $collection = $this->_quotationCollection->addFieldToFilter('main_table.id',$id);
        $collection->join(['item'=>$this->_resource->getTableName('b2b_quotation_item')],'main_table.id=item.quotation_id',
                ['item_id'=>'id','product_id','qty','product_price','proposed_price','options']);
        
        $this->_coreRegistry->register('quotation_details', $collection);
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
                $id ? __('Edit Quotation ') : __('New Quotation '), $id ? __('Edit Quotation ') : __('New Quotation ')
        );

        return $resultPage;
    }

}
