<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Model\ResourceModel\Creator\Item;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package ITvoice\AsnCreator\Model\ResourceModel\Creator\Item
 */
class Collection extends AbstractCollection
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;
    /**
     * @var
     */
    protected $purchaseOrderItemTable = 'itvoice_purchase_order_item';
    /**
     * @var bool
     */
    protected $purchaseOrderItemTableJoined = false;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->authSession = $authSession;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'ITvoice\AsnCreator\Model\Creator\Item',
            'ITvoice\AsnCreator\Model\ResourceModel\Creator\Item'
        );
    }

    /**
     * @return mixed
     */
    protected function getUserId()
    {
        return $this->authSession->getUser()->getId();
    }

    /**
     * @return Collection
     */
    protected function _beforeLoad()
    {
        if (!$this->getUserId()) {
            throw new \Exception(__('Missing user ID'));
        }

        $this->addFieldToFilter('user_id', $this->getUserId());
        $this->joinPurchaseOrderItemTable();
        return parent::_beforeLoad();
    }

    /**
     *
     */
    public function joinPurchaseOrderItemTable()
    {
        if ($this->purchaseOrderItemTableJoined) {
            return $this;
        }

        $this->getSelect()->joinLeft(
            ['po_item' => $this->getResource()->getTable($this->purchaseOrderItemTable)],
            'main_table.po_item_id = po_item.entity_id',
            [
                'door' => 'door',
                'product_id' => 'product_id'
            ],
        );

        $this->purchaseOrderItemTableJoined = true;
        return $this;
    }

    /**
     * @param $user
     */
    public function clear()
    {
        $userId = (int) $this->getUserId();
        if ($userId) {
            $where = ['user_id = ?' => $userId];
            $connection = $this->getResource()->getConnection();
            $connection->delete($this->_mainTable, $where);
        }
        return $this;
    }

    /**
     * @param $purchaseorderIte
     * @param $user
     */
    public function addPurchaseOrder($purchaseOrder)
    {
        $userId = (int) $this->getUserId();
        if ($userId) {
            $connection = $this->getResource()->getConnection();
            $data = [];
            foreach ($purchaseOrder->getItems() as $purchaseorderItem) {
                $itemQty = $purchaseorderItem->getQty();
                for ($qty = 0; $qty < $itemQty; $qty++) {
                    $data[] = [
                        'user_id' => $userId,
                        'po_item_id' => $purchaseorderItem->getId(),
                        'supplier' => $purchaseOrder->getSupplier(),
                        'product_id' => $purchaseorderItem->getProductId(),
                        'door' => $purchaseorderItem->getDoor(),
                    ];
                }
            }
            $connection->insertMultiple($this->_mainTable, $data);
        }
    }
}
