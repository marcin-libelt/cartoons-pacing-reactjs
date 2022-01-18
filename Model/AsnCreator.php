<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Model;

use ITvoice\Asn\Model\Asn;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class AsnCreator
 * @package ITvoice\AsnCreator\Model
 */
class AsnCreator
{
    /**
     * @var \ITvoice\Asn\Model\AsnFactory
     */
    protected $asnFactory;
    /**
     * @var \ITvoice\Factory\Model\FactoryRepository
     */
    protected $factoryRepository;
    /**
     * @var \Magento\SalesSequence\Model\Manager
     */
    protected $sequenceManager;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $dateFactory;
    /**
     * @var \ITvoice\Client\Model\AddressRepository
     */
    protected $addressRepository;
    /**
     * @var \ITvoice\PurchaseOrder\Model\PurchaseOrderFactory
     */
    protected $purchaseOrderFactory;
    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;
    /**
     * @var
     */
    protected $asn;
    /**
     * @var
     */
    protected $poItems;
    /**
     * @var
     */
    protected $factory;

    /**
     * AsnCreator constructor.
     * @param \ITvoice\Asn\Model\AsnFactory $asnFactory
     */
    public function __construct(
        \ITvoice\Asn\Model\AsnFactory $asnFactory,
        \ITvoice\Factory\Model\FactoryRepository $factoryRepository,
        \Magento\SalesSequence\Model\Manager $sequenceManager,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        \ITvoice\Client\Model\AddressRepository $addressRepository,
        \ITvoice\PurchaseOrder\Model\PurchaseOrderFactory $purchaseOrderFactory,
        \Magento\Framework\DB\TransactionFactory $transactionFactory
    )
    {
        $this->asnFactory = $asnFactory;
        $this->factoryRepository = $factoryRepository;
        $this->sequenceManager = $sequenceManager;
        $this->dateFactory = $dateFactory;
        $this->addressRepository = $addressRepository;
        $this->purchaseOrderFactory = $purchaseOrderFactory;
        $this->transactionFactory = $transactionFactory;
    }

    /**
     * @param Asn $asn
     * @return $this
     */
    public function setAsn(Asn $asn)
    {
        $this->asn = $asn;
        return $this;
    }

    /**
     * @return \ITvoice\Asn\Model\Asn
     */
    public function getAsn()
    {
        if ($this->asn === null) {
            $asn = $this->asnFactory->create();
            $this->setAsn($asn);
        }
        return $this->asn;
    }

    /**
     *
     */
    public function setFactoryId($factoryId)
    {
        $factory = $this->factoryRepository->getByEntityId($factoryId);
        if (!$factory) {
            throw new LocalizedException(__('Incorrect factory ID.'));
        }
        $this->factory = $factory;
        $this->getAsn()->setFactory($factory->getSupplier());
        $this->getAsn()->setFactoryCode($factory->getSageCode());
        return $this;
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function getFactory()
    {
        if (!$this->factory) {
            if ($this->asn->getId()) {
                $this->factory = $this->asn->getFactory(true);
            } else {
                throw new LocalizedException(__('Missing Factory'));
            }
        }
        return $this->factory;
    }

    /**
     * @param $packingListDate
     * @return $this
     * @throws LocalizedException
     */
    public function setPackingListDate($packingListDate)
    {
        if ($packingListDate) {
            $packingListDate = \DateTime::createFromFormat("Y-m-d", $packingListDate);
            if ($packingListDate !== false && !array_sum($packingListDate::getLastErrors())) {
                $this->getAsn()->setPackingListDate($packingListDate);
            } else {
                throw new LocalizedException(__('Incorrect date format in Packing List Date input'));
            }

        }
        return $this;
    }

    /**
     * @param $packingListNumber
     * @return $this
     */
    public function setPackingListNumber($packingListNumber)
    {
        if ($packingListNumber) {
            $this->getAsn()->setPackingListNumber($packingListNumber);
        }
        return $this;
    }

    /**
     * @param $cartonsData
     */
    public function setCartonsData($cartonsData)
    {
        if (!is_array($cartonsData)) {
            throw new LocalizedException(__('Cannot save ASN without cartons. Please add carton.'));
        }

        $this->initPoItems($cartonsData);

        $dataMap = [
            'carton_dimensions' => 'dimensions',
            'joor_so_number' => 'joorSONumber',
            'gross_weight' => 'gross_weight',
            'net_weight' => 'net_weight',
        ];


        $allCartons = $this->getAsn()->getAllCartons();
        $cartonCounter = 0;
        foreach ($allCartons as $carton) {
            $cartonNumber = (int) $carton->getCartonNumber();
            if ($cartonNumber > $cartonCounter) {
                $cartonCounter = $cartonNumber;
            }
            $carton->setDeleteThisCarton(true);
        }

        foreach ($cartonsData as $data) {

            $items = $data['items'] ?? false;
            if (!$items) {
                throw new LocalizedException(__('Cannot save empty carton. Please add item to carton %1 or remove it.', $data['cartonId']));
                continue; // TODO - we're not sure if w should save empty carton or not ?
            }

            $cartonCounter ++;
            $cartonNumber = sprintf("%02s", $cartonCounter);

            $cartonData = [];
            $cartonId = $data['cartonId'];
            if (!isset($allCartons[$cartonId])) {
                $cartonId = $cartonNumber;
                $cartonData['carton_number'] = $cartonNumber;
            }

            foreach ($dataMap as $cartonDataCode => $inputCode) {
                $cartonData[$cartonDataCode] = $data[$inputCode] ?? '';
            }

            $doorCode = $data['doorCode'] ?? '';
            $customerAddress = $this->addressRepository->getByCode($doorCode);
            $customerName = $customerAddress->getClient()->getCustomerName();
            $cartonData['customer'] = $customerName;
            $cartonData['customer_account_number'] = $customerAddress->getSageCode();
            $cartonData['destination'] = $customerAddress->getShippingMethod();
            $cartonData['door_code'] = $doorCode;

            /** @var Asn\Carton $carton */
            $carton = $this->getAsn()->addCarton($cartonId, $cartonData);
            $carton->setWarehouseLocation($customerAddress->getWarehouseLocation());

            $carton->setDeleteThisCarton(false);

            if (!$carton->getId()) {
                $carton->setInitUniqueCartonId(true);
                $cartonRealNumber = $cartonNumber;
            } else if ($data['suffix']) {
                $cartonRealNumber=  $carton->getCartonNumber();
            }

            if(isset($cartonRealNumber)) {
                $uniqueCartonId = $carton->getAsnId() . '-' . $cartonRealNumber;

                if ($this->getFactory()->getUciCode()) {
                    $uniqueCartonId .= '-' . $this->getFactory()->getUciCode();
                }

                $carton->setTemporaryUniqueCartonId($uniqueCartonId);
            }

            $carton->setSuffix($data['suffix'] ?? null);
            $carton->setAddress($customerAddress);
            $this->setItemsData($doorCode, $carton, $items);
        }
    }

    /**
     * @param Asn\Carton $carton
     * @param $itemData
     */
    protected function setItemsData($doorCode, $carton, $itemsData)
    {
        $cartonItems = $carton->getAllItems();
        foreach ($cartonItems as $item) {
            $item->setDeleteThisItem(true);
            $simpleItems = $item->getAllSimpleItems();
            foreach ($simpleItems as $simpleItem) {
                $simpleItem->setDeleteThisSimpleItem(true);
            }
        }

        foreach ($itemsData as $data) {

            $sizes = $data['sizes'] ?? [];
            $totalItemQty = 0;

            foreach ($sizes as $sizeData) {
                $productId = $data['sku'];
                $barcode = $sizeData['barcode'];
                $qty = (int) $sizeData['qty'] ?? 0;

                if (!$qty) {
                    continue;
                }

                $totalItemQty += $qty;

                $po = $data['PO'];
                /** @var \ITvoice\PurchaseOrder\Model\PurchaseOrderItem $poItem */
                $poItem = $this->getPoItem($po, $doorCode, $barcode);
                //$carton->setCustomerPo($po);
                $carton->setMbpo($po);
                $carton->setDoorName($poItem->getDoor());
                $carton->setOrderType($poItem->getOrderType());

                /** @var Asn\Item $item */
                $item = $carton->getItem($productId);
                if (!$item) {
                    $itemData = [
                        'product_id' => $productId,
                        'season' => $poItem->getSeason(),
                        'style_name' => $poItem->getStyleName(),
                        'colourway' => $poItem->getColourway(),
                        'division' => '', // @TODO for now its missing ?
                        'warehouse_location' => $carton->getWarehouseLocation()
                    ];

                    $item = $carton->addItem($productId, $itemData);
                    $item->setInitUniqueLineId(true);
                } else {
                    $item->setDeleteThisItem(false);
                }

                $simpleItemData = [
                    'size' => $poItem->getSize()
                ];

                $poInternalUsedQty = $poItem->getInternalUsedQty();
                $poInternalUsedQty += $qty;

                if ($poInternalUsedQty <= $poItem->getAvailableQty()) {
                    $poItem->setInternalUsedQty($poInternalUsedQty);
                    $poItem->setIsInternalUsedQtyUpdated(true);
                } else {
                    throw new LocalizedException(__('Internal used qty is greater than available qty for PO %1, item: %2', $po, $barcode));
                }

                $simpleItem = $item->addSimpleItem($barcode, $qty, $simpleItemData);
                $simpleItem->setDeleteThisSimpleItem(false);
                $simpleItem->setPoItem($poItem);
            }

            if (!$totalItemQty) {
                throw new LocalizedException(__('Cannot save product without sizes. Please add size to product %1 in carton %2 or remove it.', $data['sku'],  $carton->getUniqueCartonId(true)));
            }
        }
    }

    /**
     *
     */
    protected function validate()
    {
        if (!$this->getAsn()->getFactory()) {
            throw new LocalizedException(__('Factory is missing.'));
        }
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    protected function generateAsnNumber()
    {
        if ($this->getAsn()->getAsnNumber() == null) {
            $this->getAsn()->setAsnNumber(
                $this->sequenceManager->getSequence(
                    'itvoice_asn',
                    0
                )->getNextValue()
            );
        }
        return $this;
    }

    /**
     * @param $cartonsData
     * @return $this
     */
    protected function initPoItems($cartonsData)
    {
        $this->poItems = [];
        $selectedPoArray = [];

        foreach ($cartonsData as $cartonData) {
            $items = $cartonData['items'] ?? [];
            foreach ($items as $itemData) {
                $po = $itemData['PO'];
                $selectedPoArray[$po] = $po;
            }
        }

        if ($this->getAsn()->getId()) {
            $cartons = $this->getAsn()->getAllCartons();
            foreach ($cartons as $carton) {
                $items = $carton->getAllItems();
                foreach ($items as $item) {
                    $simpleItems = $item->getAllSimpleItems();
                    foreach ($simpleItems as $simpleItem) {
                        $poItem = $simpleItem->getPoItem();
                        $po = $poItem->getPurchaseOrder();
                        $internalUsedQty = max($poItem->getInternalUsedQty() - $simpleItem->getQty(), 0);
                        $poItem->setInternalUsedQty($internalUsedQty);
                        $poItem->setIsInternalUsedQtyUpdated(true);
                        $this->poItems[$po->getDocumentNo()][$poItem->getShippingDoorCode()][$poItem->getBarcode()] = $poItem;
                    }
                }
            }
        }

        $poCollection = $this->purchaseOrderFactory->create()->getCollection();
        $poCollection->addFieldToFilter('document_no', ['in' => $selectedPoArray]);
        foreach ($poCollection as $po) {
            $poItems = $po->getItems();
            foreach($poItems as $poItem) {
                if (!isset($this->poItems[$po->getDocumentNo()][$poItem->getShippingDoorCode()][$poItem->getBarcode()])) {
                    $this->poItems[$po->getDocumentNo()][$poItem->getShippingDoorCode()][$poItem->getBarcode()] = $poItem;
                }
            }
        }

        return $this;
    }

    /**
     * @param $poNumber
     * @param $doorCode
     * @param $barcode
     * @return mixed
     * @throws LocalizedException
     */
    protected function getPoItem($poNumber, $doorCode, $barcode)
    {
        if ($this->poItems === null) {
            $this->poItems = [];
        }

        if (isset($this->poItems[$poNumber][$doorCode][$barcode])) {
            return $this->poItems[$poNumber][$doorCode][$barcode];
        } else {
            throw new LocalizedException(__('Incorrect PO Item.'));
        }
    }

    /**
     *
     */
    public function create()
    {
        $this->validate();
        $this->getAsn()->setType(Asn::ASN_TYPE_INTERNAL);

        if (!$this->getAsn()->getId()) {
            $this->generateAsnNumber();
            $date = $this->dateFactory->create()->gmtDate('d/m/Y');
            $this->getAsn()->setAsnCreatedDate($date);
        }

        $this->getAsn()->setDataChanges(true);
        $transaction = $this->transactionFactory->create();
        $transaction->addObject($this->getAsn());

        if (is_array($this->poItems)) {
            foreach ($this->poItems as $poNumber => $itemsByPoNumber) {
                foreach ($itemsByPoNumber as $doorCode => $poItems) {
                    foreach ($poItems as $barcode => $poItem) {
                        if ($poItem->getIsInternalUsedQtyUpdated()) {
                            $transaction->addObject($poItem);
                        }
                    }
                }
            }
        }

        $transaction->save();

        return $this->getAsn();
    }
}
