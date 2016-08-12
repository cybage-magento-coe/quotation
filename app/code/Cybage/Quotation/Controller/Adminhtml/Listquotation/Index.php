<?php
namespace Cybage\Quotation\Controller\Adminhtml\Listquotation;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Cybage_Quotation::cybage_auotation_list');
        $resultPage->addBreadcrumb(__('Quotations'), __('List Quotations'));
        $resultPage->addBreadcrumb(__('Quotations'), __('List Quotations'));
//        $resultPage->addBreadcrumb(__('Manage Blog Posts'), __('Manage Blog Posts'));
        $resultPage->getConfig()->getTitle()->prepend(__('List Quotations'));

        return $resultPage;
    }

    /**
     * Is the user allowed to view the blog post grid.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Cybage_Quotation::cybage_auotation_list');
    }


}