<?php
namespace Cybage\Quotation\Cron;
class Expirequotation {
    protected $_quotationCollection;
    protected $_quotationHelper;
    protected $_date;
    public function __construct(\Cybage\Quotation\Model\ResourceModel\Quotation\Collection $quotationCollection, 
            \Cybage\Quotation\Helper\Data $helper,
            \Magento\Framework\Stdlib\DateTime\DateTime $date
            ) {
        $this->_quotationCollection = $quotationCollection;
        $this->_quotationHelper = $helper;
        $this->_date = $date;
    }

    public function execute() {
        $lifetime = $this->_quotationHelper->getQuotationLifeTime()*3600*24;
        if($lifetime){
            $date = strtotime($this->date->gmtDate('Y-m-d'));
            $collection = $this->_quotationCollection->addFieldToFilter('quotation_status',7);
            foreach ($collection as $value) {
                $createdDate = strtotime($value->getCreatedAt());
                if($createdDate > ($date+$lifetime)){
                    $value->setSetStatus('8');
                    $value->save();
                }
            }
        }
        return;
    }

}
