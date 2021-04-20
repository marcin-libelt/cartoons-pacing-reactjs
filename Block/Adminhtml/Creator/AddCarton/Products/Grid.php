<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Block\Adminhtml\Creator\AddCarton\Products;

use ITvoice\PurchaseOrder\Model\ResourceModel\PurchaseOrderItem;

/**
 * Class Grid
 * @package ITvoice\AsnCreator\Block\Adminhtml\Creator\AddCarton
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \ITvoice\AsnCreator\Model\ResourceModel\Creator\Products\CollectionFactory
     */
    protected $productCollectionFactory;
    /**
     * @var \ITvoice\AsnCreator\Model\ResourceModel\Creator\Item\CollectionFactory
     */
    protected $itemCollectionFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \ITvoice\AsnCreator\Model\ResourceModel\Creator\Product\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \ITvoice\AsnCreator\Model\ResourceModel\Creator\Product\CollectionFactory $productCollectionFactory,
        \ITvoice\AsnCreator\Model\ResourceModel\Creator\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->registry = $registry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        $this->setId('itvoice_asn_creator_products_grid');
        $this->setSaveParametersInSession(false);
    }

    /**
     * @return $this|Grid
     */
    protected function _prepareCollection()
    {
        $factory = $this->registry->registry('current_factory');
        $address = $this->registry->registry('current_address');

        $itemCollection = $this->itemCollectionFactory->create()
            ->addFieldToFilter('supplier', $factory->getSupplier())
            ->addFieldToFilter('main_table.door', $address->getClient()->getCustomerName())
            ->addFieldToFilter('carton_id', ['null' => true]);

        $itemCollection->getSelect()
            ->group('main_table.product_id');

        $productIds = [];

        foreach ($itemCollection as $item) {
            $productIds[] = $item->getProductId();
        }

        $collection = $this->productCollectionFactory->create();
        $collection->addFieldToFilter('product_id', ['in' => $productIds]);

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return \ITvoice\Client\Block\Adminhtml\Client\Grid|void
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id',
            [
                'header' => __('Product Id'),
                'index' => 'product_id',
            ]
        );

        parent::_prepareColumns();
    }

    /**
     * @return \ITvoice\Asn\Block\Adminhtml\Asn\Grid|void
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product_ids');

        $this->getMassactionBlock()->addItem(
            'close',
            [
                'label' => __('Close'),
                'url' => $this->getUrl(
                    '*/*/massClose',
                    ['_current' => true]
                ),
                'confirm' => __('Are you sure?')
            ]
        );
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/addCarton_products/grid', ['_current' => true]);
    }
}
