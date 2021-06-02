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
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory
    )
    {
        $this->asnFactory = $asnFactory;
        $this->factoryRepository = $factoryRepository;
        $this->sequenceManager = $sequenceManager;
        $this->dateFactory = $dateFactory;
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
            throw new LocalizedException(__('Incorrect facotry ID.'));
        }
        $this->getAsn()->setFactory($factory->getSupplier());
        return $this;
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
