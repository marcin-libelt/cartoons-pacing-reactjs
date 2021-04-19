<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Block\Adminhtml\Creator\AddCarton\Address;

/**
 * Class Grid
 * @package ITvoice\AsnCreator\Block\Adminhtml\Creator\AddCarton
 */
class Grid extends \ITvoice\Client\Block\Adminhtml\Client\Address\Grid
{
    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('itvoice_asn_creator_address_grid');
    }

    /**
     * @param $collection
     * @return $this|mixed
     */
    protected function addFilterToCollection($collection)
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/addCarton_address/grid', ['_current' => true]);
    }
}
