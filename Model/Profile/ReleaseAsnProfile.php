<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Model\Profile;

use ITvoice\Asn\Model\Asn;

/**
 * Class ReleaseAnsProfile
 * @package ITvoice\AsnCreator\Model\Profile
 */
class ReleaseAsnProfile extends \Alekseon\Dataflows\Model\Profile implements \Alekseon\Dataflows\Model\ProfileInterface
{
    /**
     * @var \ITvoice\Asn\Model\AsnFactory
     */
    protected $asnFactory;
    /**
     * @var \ITvoice\Asn\Model\AsnCsv
     */
    protected $asnCsv;

    /**
     * ReleaseAnsProfile constructor.
     * @param \Alekseon\Dataflows\Model\Profile\DataReaderFactory $dataReaderFactory
     */
    public function __construct(
        \Alekseon\Dataflows\Model\Profile\DataReaderFactory $dataReaderFactory,
        \ITvoice\Asn\Model\AsnFactory $asnFactory,
        \ITvoice\AsnCreator\Model\AsnCsv $asnCsv
    ) {
        $this->asnFactory = $asnFactory;
        $this->asnCsv = $asnCsv;
        parent::__construct($dataReaderFactory);
    }

    /**
     *
     */
    protected function getAsnCollectionToRelease()
    {
        $collection = $this->asnFactory->create()->getCollection();
        $collection->addFieldToFilter('type', Asn::ASN_TYPE_INTERNAL);
        $collection->addFieldToFilter('is_released', 0);
        return $collection;
    }

    /**
     * @return bool
     */
    public function canBeExecuted()
    {
        $collectionToRelease = $this->getAsnCollectionToRelease();
        if (!$collectionToRelease->getSize()) {
            return false;
        }
        return true;
    }

    /**
     *
     */
    public function execute()
    {
        $asnCollection = $this->getAsnCollectionToRelease();
        foreach ($asnCollection as $asn) {
            $this->exportAsn($asn);
        }
    }

    /**
     * @param $asn
     */
    protected function exportAsn($asn)
    {
        $csvContent = $this->asnCsv->getCsv($asn);
        var_dump($csvContent);
    }
}
