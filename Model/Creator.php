<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Model;

/**
 * Class Creator
 * @package ITvoice\AsnCreator\Model
 */
class Creator
{
    /**
     * @var Creator\CartonFactory
     */
    protected $cartonFactory;
    /**
     * @var Creator\ItemFactory
     */
    protected $itemFactory;
    /**
     * @var \ITvoice\PurchaseOrder\Model\PurchaseOrderFactory
     */
    protected $purchaseOrderFactory;

    /**
     * Creator constructor.
     */
    public function __construct(
        \ITvoice\AsnCreator\Model\Creator\CartonFactory $cartonFactory,
        \ITvoice\AsnCreator\Model\Creator\ItemFactory $itemFactory,
        \ITvoice\PurchaseOrder\Model\PurchaseOrderFactory $purchaseOrderFactory
    )
    {
        $this->cartonFactory = $cartonFactory;
        $this->itemFactory = $itemFactory;
        $this->purchaseOrderFactory = $purchaseOrderFactory;
    }

    /**
     *
     */
    public function prepareCreatorTablesForUser($factory)
    {
        $itemCollection = $this->itemFactory->create()->getCollection();
        $cartonCollection = $this->cartonFactory->create()->getCollection();

        $itemCollection->clear();
        $cartonCollection->clear();

        if ($factory->getId()) {
            $purchaseOrders = $this->purchaseOrderFactory->create()->getCollection();
            $purchaseOrders->addFieldToFilter('supplier', $factory->getSupplier());
            foreach ($purchaseOrders as $purchaseOrder) {
                $itemCollection->addPurchaseOrder($purchaseOrder);
            }
        }
    }
}
