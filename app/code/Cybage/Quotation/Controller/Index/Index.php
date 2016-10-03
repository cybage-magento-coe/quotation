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
namespace Cybage\Quotation\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Customer\Controller\AbstractAccount
{
    protected $_quotationHelper;
    protected $_managerinterface;
    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Cybage\Quotation\Helper\Data $data, \Magento\Framework\Message\ManagerInterface $managerinterface
    )
    {
        $this->_quotationHelper = $data;
        $this->_managerinterface = $managerinterface;
        parent::__construct($context);
    }
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($this->_quotationHelper->isActive()) {
            $this->_view->loadLayout();
            $this->_view->getLayout()->initMessages();
            $this->_view->renderLayout();
        } else {
            $this->_managerinterface->addError('This functionality is not available now');
            $resultRedirect->setPath('customer/account/');
            return $resultRedirect;
        }
    }
}
