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
     * @var \ITvoice\AsnCreator\Model\Creator\ItemFactory
     */
    protected $creatorItemFactory;
    /**
     * @var
     */
    protected $clientFactory;
    /**
     * @var \ITvoice\Client\Model\Client\AddressFactory
     */
    protected $clientAddressFactory;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \ITvoice\Client\Model\ResourceModel\Client\Address\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \ITvoice\Client\Model\ResourceModel\Client\Address\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $registry,
        \ITvoice\AsnCreator\Model\Creator\ItemFactory $creatorItemFactory,
        \ITvoice\Client\Model\ClientFactory $clientFactory,
        \ITvoice\Client\Model\Client\AddressFactory $clientAddressFactory,
        array $data = []
    )
    {
        $this->creatorItemFactory = $creatorItemFactory;
        $this->clientFactory = $clientFactory;
        $this->clientAddressFactory = $clientAddressFactory;
        parent::__construct($context, $backendHelper, $collectionFactory, $registry, $data);
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('itvoice_asn_creator_address_grid');
        $this->setSaveParametersInSession(false);
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
        $itemCollection = $this->creatorItemFactory->create()->getCollection();
        $itemCollection->addFieldToFilter('carton_id', ['null' => true]);
        $itemCollection->getSelect()->group('main_table.door');

        $customerNames = [];
        foreach ($itemCollection as $item) {
            $customerNames[$item->getDoor()] = $item->getDoor();
        }

        $clientCollection = $this->clientFactory->create()->getCollection();
        $clientCollection->addFieldToFilter('customer_name', ['in' => $customerNames]);

        $clientIds = [];
        foreach ($clientCollection as $client) {
            $clientIds[] = $client->getId();
        }

        $collection->addFieldToFilter('client_id', ['in' => $clientIds]);

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
