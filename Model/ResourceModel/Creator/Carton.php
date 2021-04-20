<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Model\ResourceModel\Creator;

/**
 * Class Carton
 * @package ITvoice\AsnCreator\Model\ResourceModel\Cretor
 */
class Carton extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('itvoice_asn_creator_carton', 'entity_id');
    }
}
