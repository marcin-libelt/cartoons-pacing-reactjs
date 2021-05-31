<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Controller\Adminhtml\Factory;

use Magento\Backend\App\Action\Context;

/**
 * Class GetItems
 * @package ITvoice\AsnCreator\Controller\Adminhtml\Factory
 */
class GetItems extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;
    /**
     * @var \ITvoice\Factory\Model\FactoryRepository
     */
    protected $factoryRepository;
    /**
     * @var \ITvoice\PurchaseOrder\Model\PurchaseOrderFactory
     */
    protected $purchaseOrderFactory;
    /**
     * @var \ITvoice\PurchaseOrder\Model\PurchaseOrderItem
     */
    protected $purchaseOrderItemFactory;

    /**
     * GetItems constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \ITvoice\Factory\Model\FactoryRepository $factoryRepository
     * @param \ITvoice\PurchaseOrder\Model\PurchaseOrderFactory $purchaseOrderFactory
     * @param \ITvoice\PurchaseOrder\Model\PurchaseOrderItemFactory $purchaseOrderItemFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \ITvoice\Factory\Model\FactoryRepository $factoryRepository,
        \ITvoice\PurchaseOrder\Model\PurchaseOrderFactory $purchaseOrderFactory,
        \ITvoice\PurchaseOrder\Model\PurchaseOrderItemFactory $purchaseOrderItemFactory
    ) {
        $this->factoryRepository = $factoryRepository;
        $this->jsonFactory = $jsonFactory;
        $this->purchaseOrderFactory = $purchaseOrderFactory;
        $this->purchaseOrderItemFactory = $purchaseOrderItemFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_forward('denied');
        }

        $jsonResponse = $this->jsonFactory->create();
        $data = [];

        $purchaseOrders = $this->getPurchaseOrders();
        $purchaseOrderIds = array_keys($purchaseOrders);
        $poItems = $this->purchaseOrderItemFactory->create()->getCollection();
        $poItems->addFieldToFilter('purchase_order_id', ['in' => $purchaseOrderIds]);

        $idMap = [];
        $limit = 0;
        foreach ($poItems as $poItem) {
            $qty = (int) $poItem->getBalanceQty();
            if ($qty <= 0) {
                continue;
            }

            $shippingDoorCode = $poItem->getShippingDoorCode();
            $purchaseOrder = $purchaseOrders[$poItem->getPurchaseOrderId()];
            $productId = $poItem->getproductId();

            $itemId = $shippingDoorCode . '-' . $productId . $purchaseOrder->getId();

            if (!isset($idMap[$itemId])) {
                $shippingAddress =  $poItem->getShippingAddress();
                $client = $shippingAddress->getClient();

                $rowId = $idMap[$itemId] = $poItem->getId();
                $data[$rowId] = [
                    'doorLabel' => $poItem->getDoor(),
                    'doorCode' => $shippingDoorCode,
                    'PO' => $purchaseOrder->getDocumentNo(),
                    'name' => $poItem->getStyleName(),
                    'sku' => $productId,
                    'joorSONumber' => $purchaseOrder->getJoorSoNumber(),
                    'orderType' => $poItem->getOrderType(),
                    'unit_selling_price' => $poItem->getUnitSellingPrice(),
                    'clientName' => $client->getCustomerName(),
                    'warehouseLocation' => $shippingAddress->getWarehouseLocation(),
                    'sizes' => [],
                ];
            } else {
                $rowId = $idMap[$itemId];
            }

            $data[$rowId]['sizes'][] = [
                'qty' => $qty,
                'barcode' => $poItem->getBarcode(),
                'size' => $poItem->getSize(),
            ];

             $limit += 1;  // TODO remove this limitation for PRODUCTION env
             if ($limit == 100) {
                 break;
             }
        }

        $jsonResponse->setData(array_values($data));
        return $jsonResponse;
    }

    /**
     *
    */
    protected function getPurchaseOrders()
    {
        $purchaseOrder = [];
        $factoryId = $this->getRequest()->getParam('factory_id');
        $factory = $this->factoryRepository->getByEntityId($factoryId);
        if ($factory) {
            $poCollection = $this->purchaseOrderFactory->create()->getCollection();
            $poCollection->addFieldToFilter('supplier', $factory->getSupplier());

            foreach ($poCollection as $po) {
                $purchaseOrder[$po->getId()] = $po;
            }
        }

        return $purchaseOrder;
    }
}
