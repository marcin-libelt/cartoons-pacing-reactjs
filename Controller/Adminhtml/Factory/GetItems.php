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
             $doorCode = $poItem->getDoor();  // @todo replace with door code, for now we dont have door code
             $purchaseOrder = $purchaseOrders[$poItem->getPurchaseOrderId()];
             $productId = $poItem->getproductId();

             $itemId = $doorCode . '-' . $productId . $purchaseOrder->getId();

             if (!isset($idMap[$itemId])) {
                 $rowId = $idMap[$itemId] = $poItem->getId();
                 $data[$rowId] = [
                     'id' => $rowId,
                     'doorLabel' => $poItem->getDoor(),
                     'doorCode' => $doorCode,
                     'PO' => $purchaseOrder->getDocumentNo(),
                     'name' => $poItem->getStyleName(),
                     'sku' => $productId,
                     'sizes' => [],
                     'type' => 'style'
                 ];
             } else {
                 $rowId = $idMap[$itemId];
             }


             $data[$rowId]['sizes'][] = [
                 'qty' => $poItem->getQty(),
                 'barcode' => $poItem->getSkuid(), // @todo we dont have it, for now its skuid ?
                 'size' => $poItem->getSize(),
             ];

             $limit += 1;  // TODO remove this limitation for PRODUCTION env
             if($limit == 100) {
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
        $factory = $this->factoryRepository->getById($factoryId);
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
