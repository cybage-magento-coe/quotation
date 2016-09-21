<?php

namespace Cybage\Quotation\Model\Source;

class Status implements \Magento\Framework\Data\OptionSourceInterface
{

    protected $_quotationHelper;

    public function __construct(\Cybage\Quotation\Helper\Data $data)
    {
        $this->_quotationHelper = $data;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->_quotationHelper->getQuotationStatusArray() as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

}
