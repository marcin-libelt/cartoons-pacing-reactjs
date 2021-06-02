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
     * @var
     */
    protected $asn;

    /**
     * AsnCreator constructor.
     * @param \ITvoice\Asn\Model\AsnFactory $asnFactory
     */
    public function __construct(
        \ITvoice\Asn\Model\AsnFactory $asnFactory,
        \ITvoice\Factory\Model\FactoryRepository $factoryRepository,
        \Magento\SalesSequence\Model\Manager $sequenceManager,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        \ITvoice\Client\Model\AddressRepository $addressRepository
    )
    {
        $this->asnFactory = $asnFactory;
        $this->factoryRepository = $factoryRepository;
        $this->sequenceManager = $sequenceManager;
        $this->dateFactory = $dateFactory;
        $this->addressRepository = $addressRepository;
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
        $this->getAsn()->setFactory($factory->getSupplier());
        return $this;
    }

    /**
     * @param $cartonsData
     */
    public function setCartonsData($cartonsData)
    {
        $dataMap = [
            'carton_dimensions' => 'dimensions',
            'joor_so_number' => 'joorSONumber',
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

            /**
             * @TODO: how to fill this data ?
             */
            $cartonData['customer_po'] = '';
            $cartonData['door_name'] = '';
            $cartonData['store_code'] = '';

            $carton = $this->getAsn()->addCarton($cartonNumber, $cartonData);
            $carton->setAddress($customerAddress);

            $items = $data['items'] ?? [];
            $this->setItemsData($carton, $items);
        }
    }

    /**
     * @param $carton
     * @param $itemData
     */
    protected function setItemsData($carton, $itemsData)
    {
        $itemsCounter = 0;
        foreach ($itemsData as $data) {

            $sizes = $data['sizes'] ?? [];

            foreach ($sizes as $sizeData) {
                $itemsCounter++;
                $qty = (int) $sizeData['qty'] ?? 0;

                $itemData = [
                    'product_id' => $data['sku'] ?? '',
                    'qty' => $qty,
                    'warehouse_location' => $carton->getAddress()->getWarehouseLocation(),
                    'season' => '', // @TODO for now its missing ?
                    'style_name' => '', // @TODO for now its missing ?
                    'colourway' => '', // @TODO for now its missing ?
                    'division' => '', // @TODO for now its missing ?
                ];

                $carton->addItem($itemsCounter, $itemData);
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
     *
     */
    public function create()
    {
        $this->validate();
        $this->getAsn()->setType(Asn::ASN_TYPE_INTERNAL);
        $this->generateAsnNumber();
        $date = $this->dateFactory->create()->gmtDate('Y-m-d');
        $this->getAsn()->setAsnCreatedDate($date);
        //$this->getAsn()->save();
        return $this->getAsn();
    }
}
