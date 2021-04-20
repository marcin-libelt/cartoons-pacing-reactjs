<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Model\ResourceModel\Creator;

/**
 * Class Item
 * @package ITvoice\AsnCreator\Model\ResourceModel\Creator
 */
class Item extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('itvoice_asn_creator_item', 'entity_id');
    }
}
