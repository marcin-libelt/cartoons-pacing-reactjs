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
class GetItems extends \ITvoice\Asn\Controller\Adminhtml\Asn
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
     * @var array
     */
    protected $itemIdMap = [];
    /**
     * @var array
     */
    protected $poItemsByRowId = [];

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
        \ITvoice\PurchaseOrder\Model\PurchaseOrderItemFactory $purchaseOrderItemFactory,
        \Magento\Framework\Registry $registry,
        \ITvoice\Asn\Model\AsnRepository $asnRepository
    ) {
        $this->factoryRepository = $factoryRepository;
        $this->jsonFactory = $jsonFactory;
        $this->purchaseOrderFactory = $purchaseOrderFactory;
        $this->purchaseOrderItemFactory = $purchaseOrderItemFactory;
        parent::__construct($context, $registry, $asnRepository);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_forward('denied');
        }

        if ($this->getRequest()->getParam('asn_id')) {
            $asn = $this->initAsn('asn_id');
        } else {
            $asn = false;
        }

        $jsonResponse = $this->jsonFactory->create();

        $purchaseOrders = $this->getPurchaseOrders();
        $purchaseOrderIds = array_keys($purchaseOrders);
        $poItems = $this->purchaseOrderItemFactory->create()->getCollection();
        $poItems->addFieldToFilter('purchase_order_id', ['in' => $purchaseOrderIds]);
        $poItems->getSelect()->where('(balance_qty >= order_qty and balance_qty > 0) or (internal_used_qty < qty && internal_used_qty > 0)');

        $orders = [];
        foreach ($poItems as $poItem) {
            $qty = (int) max(0, $poItem->getQty() - $poItem->getInternalUsedQty());

            $shippingDoorCode = $poItem->getShippingDoorCode();
            $purchaseOrder = $purchaseOrders[$poItem->getPurchaseOrderId()];
            $productId = $poItem->getProductId();

            $itemId = $shippingDoorCode . '-' . $productId . '_' . $purchaseOrder->getId();

            if (!isset($this->itemIdMap[$itemId])) {
                $shippingAddress =  $poItem->getShippingAddress();
                $client = $shippingAddress->getClient();

                $rowId = $this->itemIdMap[$itemId] = $poItem->getId();
                $this->poItemsByRowId[$rowId] = $poItem;
                $orders[$rowId] = [
                    'id' => $rowId,
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
                    'type' => 'style',
                    'store_code' => $purchaseOrder->getStoreCode(),
                    'customer_po' => $purchaseOrder->getCustomerPo(),
                    'colourway' => $poItem->getColourway(),
                    'cites' => $poItem->getCites(),
                    'fish_wildlife' => $poItem->getFishWildlife(),
                ];
            } else {
                $rowId = $this->itemIdMap[$itemId];
            }

            $orders[$rowId]['sizes'][] = [
                'qty' => $qty,
                'barcode' => $poItem->getBarcode(),
                'size' => $poItem->getSize(),
                'comments' => $poItem->getComments(),
            ];
        }

        $data = [];
        if ($asn) {
            $asnData = $this->getAsnData($asn, $orders);
            $this->addAsnDataToOrders($asnData, $orders);
            $data['asn'] = $asnData;
        }

        $this->sortSizes($orders);

        $data['cartons'] = $this->getFactoryCartons();
        $data['orders'] = array_values($orders);

        $jsonResponse->setData($data);
        return $jsonResponse;
    }

    /**
     * @return false|mixed
     */
    protected function getFactory()
    {
        $factoryId = $this->getRequest()->getParam('factory_id');
        $factory = $this->factoryRepository->getByEntityId($factoryId);
        return $factory;
    }

    /**
     *
    */
    protected function getPurchaseOrders()
    {
        $purchaseOrder = [];
        $factory = $this->getFactory();
        if ($factory) {
            $poCollection = $this->purchaseOrderFactory->create()->getCollection();
            $poCollection->addFieldToFilter('supplier', $factory->getSupplier());

            foreach ($poCollection as $po) {
                $purchaseOrder[$po->getId()] = $po;
            }
        }

        return $purchaseOrder;
    }

    /**
     *
     */
    protected function getFactoryCartons()
    {
        $factoryCartons = $this->getFactory()->getCartonCollection();
        $cartons = [];
        foreach ($factoryCartons as $carton) {
            $cartons[] = $carton->getDimensions();
        }

        return $cartons;
    }

    /**
     * @param $asn
     * @return array
     */
    protected function getAsnData($asn, $orders)
    {
        $cartons = [];

        foreach ($asn->getAllCartons() as $carton) {

            $items = [];
            foreach ($carton->getAllItems() as $item) {

                $sizes = [];
                $rowId = false;
                $barcodesInItem = [];

                foreach ($item->getAllSimpleItems() as $simpleItem) {

                    $barcodesInItem[$simpleItem->getBarcode()] = $simpleItem->getBarcode();

                    $sizes[] = [
                        'qty' => (int)$simpleItem->getQty(),
                        'size' => $simpleItem->getSize(),
                        'barcode' => $simpleItem->getBarcode(),
                    ];

                    if (!$rowId) {
                        $poItem = $simpleItem->getPoItem();
                        $purchaseOrder = $poItem->getPurchaseOrder();
                        $itemId = $carton->getDoorCode() . '-' . $item->getProductId() . '_' . $purchaseOrder->getId();
                        if (isset($this->itemIdMap[$itemId])) {
                            $rowId = $this->itemIdMap[$itemId];
                        } else {
                            $rowId = $this->itemIdMap[$itemId] = 'asn_item_' . $item->getId();
                            $this->poItemsByRowId[$rowId] = $simpleItem->getPoItem();
                        }
                    }
                }

                if (isset($orders[$rowId]['sizes'])) {
                    foreach ($orders[$rowId]['sizes'] as $size) {
                        if (!isset( $barcodesInItem[$size['barcode']])) {
                            $size['qty'] = 0;
                            $sizes[] = $size;
                        }
                    }
                }

                $items[] = [
                    'id' => $rowId,
                    'PO' => $carton->getMbpo(),
                    'sku' => $item->getProductId(),
                    'sizes' => $sizes,
                ];
            }

            $this->sortSizes($items);

            $carton = [
                'cartonId' => $carton->getUniqueCartonId(),
                'doorCode' => $carton->getDoorCode(),
                'gross_weight' => $carton->getGrossWeight(),
                'net_weight' => $carton->getNetWeight(),
                'dimensions' => $carton->getCartonDimensions(),
                'suffix' => $carton->getSuffix(),
                'joorSONumber' => $carton->getJoorSoNumber(),
                'PO' => $carton->getCustomerPo(),
                'items' => $items
            ];

            $cartons[] = $carton;
        }

        $asnData = [
            'cartons' => $cartons,
            'packing_list_number' => $asn->getPackingListNumber(),
            'packing_list_date' => $asn->getPackingListDate(),
            'is_first_cost' => $asn->getIsFirstCost(),
            'factory_id' => $asn->getFactory(true)->getId(),
        ];
        return $asnData;
    }

    /**
     * @param $asnData
     * @param $orders
     */
    protected function addAsnDataToOrders($asnData, &$orders)
    {
        $cartons = $asnData['cartons'];
        foreach ($cartons as $carton) {
            $items = $carton['items'];
            foreach ($items as $item) {
                $rowId = $item['id'];

                $existingBarcodes = [];
                if (isset($orders[$rowId]['sizes'])) {
                    foreach ($orders[$rowId]['sizes'] as $size) {
                        $existingBarcodes[$size['barcode']] = $size['barcode'];
                    }
                }

                $sizes = $item['sizes'];
                foreach ($sizes as $size) {
                    if (!isset($orders[$rowId])) {
                        $poItem = $this->poItemsByRowId[$rowId];
                        $purchaseOrder = $poItem->getPurchaseOrder();
                        $shippingDoorCode = $poItem->getShippingDoorCode();
                        $productId = $poItem->getProductId();
                        $shippingAddress =  $poItem->getShippingAddress();
                        $client = $shippingAddress->getClient();

                        $orders[$rowId] = [
                            'id' => $rowId,
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
                            'type' => 'style',
                            'store_code' => $purchaseOrder->getStoreCode(),
                            'customer_po' => $purchaseOrder->getCustomerPo(),
                            'colourway' => $poItem->getColourway(),
                            'cites' => $poItem->getCites(),
                            'fish_wildlife' => $poItem->getFishWildlife(),
                        ];
                    }

                    if (!isset($existingBarcodes[$size['barcode']])) {
                        $orders[$rowId]['sizes'][] = [
                            'qty' => 0,
                            'barcode' => $size['barcode'],
                            'size' => $size['size'],
                        ];

                        $existingBarcodes[$size['barcode']] = $size['barcode'];
                    }
                }
            }
        }
    }

    protected function sortSizes(&$orders)
    {
        foreach ($orders as $rowId => $data) {
            $sizes = $data['sizes'];
            usort($sizes, [$this, 'sizeSortCompare']);
            $orders[$rowId]['sizes'] = $sizes;
        }
    }

    /**
     * @param $sizeA
     * @param $sizeB
     * @return bool
     */
    protected function sizeSortCompare($sizeA, $sizeB)
    {
        $sizeNumberA = (float) $sizeA['size'];
        $sizeNumberB = (float) $sizeB['size'];
        return $sizeNumberA > $sizeNumberB;
    }
}
