<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Block\Adminhtml\Creator\SelectAddress;

/**
 * Class Grid
 * @package ITvoice\AsnCreator\Block\Adminhtml\Creator\SelectAddress
 */
class Grid extends \ITvoice\Client\Block\Adminhtml\Client\Address\Grid
{
    /**
     * @var \ITvoice\Factory\Model\ResourceModel\Factory\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('itvoice_asncreator_address_grid');
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
                'renderer' => \ITvoice\AsnCreator\Block\Adminhtml\Creator\SelectAddress\Grid\Renderer\SelectButton::class,
                'filter' => false,
                'sortable' => false,
            ]
        );
    }

    /**
     * @return false
     */
    public function getGridUrl()
    {
        return $this->getUrl('itvoice_asn_creator/SelectAddress/Grid', ['_current' => true]);
    }
}
