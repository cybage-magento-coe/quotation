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

class Converttoquote extends \Cybage\Quotation\Controller\Add\Index
{
    protected $_checkoutSession;
    protected $_quoteItem;
    protected $_productHelper;
    protected $_bundleConfiguration;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Cybage\Quotation\Model\Quotation $quotation, \Magento\Customer\Model\Session $customer, \Cybage\Quotation\Model\QuotationItem $quotationItem, \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator, \Magento\Catalog\Model\Product $product, \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProduct, \Magento\Framework\Message\ManagerInterface $managerinterface, \Magento\Framework\Event\ManagerInterface $event, \Cybage\Quotation\Helper\Data $data, \Magento\Checkout\Model\Session $checkoutSession, \Magento\Quote\Model\Quote\Item $quoteItem, \Cybage\Quotation\Helper\Configuration $productHelper, \Cybage\Quotation\Helper\Bundle\Configuration $bundleConfiguration)
    {
        $this->_checkoutSession = $checkoutSession;
        $this->_quoteItem = $quoteItem;
        $this->_productHelper = $productHelper;
        $this->_bundleConfiguration = $bundleConfiguration;
        parent::__construct($context, $quotation, $customer, $quotationItem, $formKeyValidator, $product, $configurableProduct, $managerinterface, $event, $data);
    }
    public function execute()
    {
        //$data = $this->getRequest()->getParams();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        // Your code
        $products = $this->deleteQuoteItems();
        try {
            $this->_quotationId = $this->activeQuotationId();
            if (!$this->_quotationId) {
                $this->_quotationId = $this->createQuotation();
            }
            if ($this->_quotationId && !empty($products)) {
                foreach ($products as $key => $value) {
                    $this->addQuotationItem($value);
                }
            }
            $this->_managerinterface->addSuccess('Product successfully added to quotation');
            $this->_event->dispatch('btob_quotation_item_update_after', ['id' => $this->_quotationId]);
        } catch (Exception $exc) {
            $this->_managerinterface->addError($exc->getMessage());
        }
////      
        $resultRedirect->setPath('quotation/view/index', ['id' => $this->_quotationId]);
        return $resultRedirect;
    }
    /**
     * Deletes existing cart items
     */
    private function deleteQuoteItems()
    {
        $checkoutSession = $this->_checkoutSession;
        $allItems = $checkoutSession->getQuote()->getAllVisibleItems();
        $i = 0;
        $products = [];
        $quote;
        foreach ($allItems as $item) {
            $options = [];
            $type = $item->getProduct()->getTypeId();
            if ($type == 'configurable') {
                $_customOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                $options = $_customOptions['info_buyRequest']['super_attribute'];
                $products[$i]['super_attribute'] = $options;
            } elseif ($type == 'bundle') {
                $options = $this->_bundleConfiguration->getBundleOptions($item);
                $products[$i]['bundle_option'] = $options['bundle_option'];
                $products[$i]['bundle_option_qty'] = $options['bundle_option_qty'];
            } else {
                $tmp = $this->_productHelper->getCustomOptions($item);
                if (is_array($tmp)) {
                    foreach ($tmp as $value) {
                        $options[$value['option_id']] = $value['value_id'];
                    }
                }
                $products[$i]['options'] = $options;
            }
            $products[$i]['product'] = $item->getProduct()->getId();
            $products[$i]['qty'] = $item->getQty();
            $i++;
            $quote = $item->getQuote();
            $item->delete();
        }
        $quote->delete();
        return $products;
    }
}
