<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Block\Adminhtml\Creator\SelectFactory;

/**
 * Class Grid
 * @package ITvoice\AsnCreator\Block\Adminhtml\Creator\SelectFactory
 */
class Grid extends \ITvoice\Factory\Block\Adminhtml\Factory\Grid
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
        $this->setId('itvoice_asncreator_factory_grid');
    }

    /**
     * @return \ITvoice\Client\Block\Adminhtml\Client\Grid|void
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumnAfter(
            'button',
            [
                'header' => '',
                'index' => '',
                'renderer' => \ITvoice\AsnCreator\Block\Adminhtml\Creator\SelectFactory\Grid\Renderer\SelectButton::class,
                'filter' => false,
                'sortable' => false,
            ],
            'supplier'
        );

        $this->sortColumnsByOrder();
    }

    /**
     * @param \Magento\Catalog\Model\Entity|\Magento\Framework\DataObject $item
     * @return false
     */
    public function getRowUrl($item)
    {
        return false;
    }
}
