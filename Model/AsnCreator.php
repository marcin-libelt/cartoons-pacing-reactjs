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
     * @return \ITvoice\Asn\Model\Asn
     */
    public function getAsn()
    {
        if ($this->asn === null) {
            $this->asn = $this->asnFactory->create();
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
     */
    public function getFactory()
    {
        if (!$this->factory) {
            throw new LocalizedException(__('Missing Factory'));
        }
        return $this->factory;
    }

    /**
     * @param $invoiceAmount
     */
    public function setInvoiceAmount($invoiceAmount)
    {
        if ($invoiceAmount) {
            $this->getAsn()->setInvoiceAmount((float)$invoiceAmount);
        }
        return $this;
    }

    /**
     * @param $invoiceAmount
     */
    public function setInvoiceNumber($invoiceNumber)
    {
        if ($invoiceNumber) {
            $this->getAsn()->setInvoiceNumber($invoiceNumber);
        }
        return $this;
    }

    /**
     * @param $cartonsData
     */
    public function setCartonsData($cartonsData)
    {
        $this->initPoItems($cartonsData);

        $dataMap = [
            'carton_dimensions' => 'dimensions',
            'joor_so_number' => 'joorSONumber',
            'gross_weight' => 'gross_weight',
            'net_weight' => 'net_weight',
        ];

        $cartonCounter = 0;
        foreach ($cartonsData as $data) {
            $cartonCounter ++;
            $cartonNumber = sprintf("%02s", $cartonCounter);

            $cartonData = [];
            foreach ($dataMap as $cartonDataCode => $inputCode) {
                $cartonData[$cartonDataCode] = $data[$inputCode] ?? '';
            }

            $cartonData['carton_number'] = $cartonNumber;

            $doorCode = $data['doorCode'] ?? '';
            $customerAddress = $this->addressRepository->getByCode($doorCode);
            if (!$customerAddress->getId()) {
                throw new LocalizedException(__('Address with code "%1" does not exists.', $doorCode));
            }
            $customerName = $customerAddress->getClient()->getCustomerName();
            $cartonData['customer'] = $customerName;
            $cartonData['customer_account_number'] = $customerAddress->getSageCode();
            $cartonData['destination'] = $customerAddress->getShippingMethod();
            $cartonData['door_code'] = $doorCode;

            $carton = $this->getAsn()->addCarton($cartonNumber, $cartonData);
            $carton->setInitUniqueCartonId(true);

            $sufix =  '-' . $cartonNumber;
            if ($this->getFactory()->getUciCode()) {
                $sufix .= '-' . $this->getFactory()->getUciCode();
            }
            if ($data['suffix']) {
                $sufix .= '-' . $data['suffix'];
            }

            $carton->setSuffix($sufix);
            $carton->setAddress($customerAddress);

            $items = $data['items'] ?? [];
            $this->setItemsData($doorCode, $carton, $items);
        }
    }

    /**
     * @param $carton
     * @param $itemData
     */
    protected function setItemsData($doorCode, $carton, $itemsData)
    {
        foreach ($itemsData as $data) {

            $sizes = $data['sizes'] ?? [];

            foreach ($sizes as $sizeData) {
                $productId = $data['sku'];
                $barcode = $sizeData['barcode'];
                $qty = (int) $sizeData['qty'] ?? 0;

                if (!$qty) {
                    continue;
                }

                $po = $data['PO'];
                $poItem = $this->getPoItem($po, $doorCode, $barcode);
                $carton->setCustomerPo($po);

                $item = $carton->getItem($productId);
                if (!$item) {
                    $itemData = [
                        'product_id' => $productId,
                        'warehouse_location' => $carton->getAddress()->getWarehouseLocation(),
                        'season' => $poItem->getSeason(),
                        'style_name' => $poItem->getStyleName(),
                        'colourway' => $poItem->getColourway(),
                        'division' => '', // @TODO for now its missing ?
                    ];

                    $item = $carton->addItem($productId, $itemData);
                    $item->setInitUniqueLineId(true);
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
                $simpleItem->setPoItem($poItem);
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
        $selectedPoArray = [];
        foreach ($cartonsData as $cartonData) {
            $items = $cartonData['items'] ?? [];
            foreach ($items as $itemData) {
                $po = $itemData['PO'];
                $selectedPoArray[$po] = $po;
            }
        }

        $poCollection = $this->purchaseOrderFactory->create()->getCollection();
        $poCollection->addFieldToFilter('document_no', ['in' => $selectedPoArray]);
        foreach ($poCollection as $po) {
            $poItems = $po->getItems();
            foreach($poItems as $item) {
                $this->poItems[$po->getDocumentNo()][$item->getShippingDoorCode()][$item->getBarcode()] = $item;
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
        $this->generateAsnNumber();
        $date = $this->dateFactory->create()->gmtDate('d/m/Y');
        $this->getAsn()->setAsnCreatedDate($date);

        $transaction = $this->transactionFactory->create();
        $transaction->addObject($this->getAsn());

        foreach ($this->poItems as $poNumber => $itemsByPoNumber) {
            foreach ($itemsByPoNumber as $doorCode => $poItems) {
                foreach ($poItems as $barcode => $poItem) {
                    if ($poItem->getIsInternalUsedQtyUpdated()) {
                        $transaction->addObject($poItem);
                    }
                }
            }
        }

        $transaction->save();

        return $this->getAsn();
    }
}
