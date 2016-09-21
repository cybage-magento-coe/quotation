<?php
namespace Cybage\Quotation\Block\Adminhtml\Quotation\Edit;

/**
 * Adminhtml blog post edit form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    protected $_quotationHelper;
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, \Magento\Store\Model\System\Store $systemStore, array $data = [], \Cybage\Quotation\Helper\Data $helper)
    {
        $this->_systemStore = $systemStore;
        $this->_quotationHelper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('post_form');
        $this->setTitle(__('Post Information'));
    }
    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Ashsmith\Blog\Model\Post $model */
        $model = $this->_coreRegistry->registry('quotation_details');
        $tmp = $model->getData();
        /** @var \Magento\Framework\Data\Form $form */
        if ($tmp[0]['quotation_status'] != 6) {
            $form = $this->_formFactory->create(
                    ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
            );
            $form->setHtmlIdPrefix('post_');
            $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Quotation Information'), 'class' => 'fieldset-wide']);
            $fieldset->addField('delivery_date', 'text', ['label' => __('Delivery Date'),'title' => __('Delivery Date'),'disabled' => 'disabled','name' => 'delivery_date','required' => true,]);
            $fieldset->addField('quotation_status', 'select', ['label' => __('Status'),'title' => __('Status'),'name' => 'quotation_status','required' => true,'options' => $this->_quotationHelper->getQuotationStatusArray()]);
            $fieldset->addField('comment', 'editor', ['name' => 'comment','label' => __('Content'),'title' => __('Content'),'style' => 'height:36em','required' => true]);
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
            $form->setValues($tmp[0]);
            $form->setUseContainer(true);
            $this->setForm($form);
        }
        return parent::_prepareForm();
    }
}
