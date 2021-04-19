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
     * @return \ITvoice\Client\Block\Adminhtml\Client\Grid|void
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn(
            'button',
            [
                'header' => '',
                'index' => '',
                'renderer' => \ITvoice\AsnCreator\Block\Adminhtml\Creator\AddCarton\Address\Grid\Renderer\SelectButton::class,
                'filter' => false,
                'sortable' => false,
            ]
        );
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
