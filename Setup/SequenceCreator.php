<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Setup;

use Magento\SalesSequence\Model\Builder;
use Magento\SalesSequence\Model\Config as SequenceConfig;

/**
 * Class SequenceCreator
 * @package ITvoice\AsnCreator\Setup
 */
class SequenceCreator
{
    /**
     * @var Builder
     */
    private $sequenceBuilder;

    /**
     * @var SequenceConfig
     */
    private $sequenceConfig;

    public function __construct(
        Builder $sequenceBuilder,
        SequenceConfig $sequenceConfig
    ) {
        $this->sequenceBuilder = $sequenceBuilder;
        $this->sequenceConfig = $sequenceConfig;
    }

    /**
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function create()
    {
        $entityType = 'itvoice_asn';
        $storeId = 0;
        $this->sequenceBuilder
            ->setPrefix('1')
            ->setSuffix($this->sequenceConfig->get('suffix'))
            ->setStartValue($this->sequenceConfig->get('startValue'))
            ->setStoreId($storeId)
            ->setStep($this->sequenceConfig->get('step'))
            ->setWarningValue($this->sequenceConfig->get('warningValue'))
            ->setMaxValue($this->sequenceConfig->get('maxValue'))
            ->setEntityType($entityType)->create();
    }
}
