<?php

namespace Cybage\Quotation\Controller\Adminhtml\View;

use Magento\Backend\App\Action;
use \Cybage\Quotation\Helper;

class Save extends \Magento\Backend\App\Action
{

    protected $_quotationComment;
    protected $_quotation;
    protected $authSession;
    protected $_managerinterface;

    public function __construct(Action\Context $context, \Cybage\Quotation\Model\QuotationComment $quotationComment, \Cybage\Quotation\Model\Quotation $quotation, \Magento\Backend\Model\Auth\Session $authSession, \Magento\Framework\Message\ManagerInterface $managerinterface)
    {
        $this->_quotationComment = $quotationComment;
        $this->_quotation = $quotation;
        $this->authSession = $authSession;
        $this->_managerinterface = $managerinterface;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();
        try {
            $resultRedirect = $this->resultRedirectFactory->create();
            $quotation = $this->_quotation->load($data['id']);
           
            if (\Cybage\Quotation\Helper\Data::STATUS_INT != $quotation->getQuotationStatus() /*&& $quotation->getTotalProposedPrice() > 0*/) {
                $quotation->setQuotationStatus($data['quotation_status'])->save();
                $this->_quotationComment->setQuotationId($data['id']);
                $this->_quotationComment->setAdminId($this->authSession->getUser()->getId());
                $this->_quotationComment->setCustomerId($quotation->getCustomerId());
                $this->_quotationComment->setComment($data['comment']);
                $this->_quotationComment->save();
                $this->_managerinterface->addSuccess(__('Quotation successfully updated.'));
            } else {
                $this->_managerinterface->addError(__('Quotation is in intermediate state so not able to save it.'));
            }
        } catch (Exception $exc) {
            $this->_managerinterface->addError($exc->getMessage());
        }
        return $resultRedirect->setPath('quotation/view/index/id/' . $data['id']);
    }

}
