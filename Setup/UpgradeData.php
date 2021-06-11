<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class UpgradeData
 * @package ITvoice\AsnCreator\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var SequenceCreator
     */
    protected $sequenceCreator;
    /**
     * @var \ITvoice\AsnVerification\Model\AsnFactoryItemFactory
     */
    protected $asnFactoryItemFactory;
    /**
     * @var \ITvoice\Asn\Model\AsnFactory
     */
    protected $asnFactory;

    /**
     * UpgradeData constructor.
     * @param SequenceCreator $sequenceCreator
     * @param \ITvoice\AsnVerification\Model\AsnFactoryItemFactory $asnFactoryItemFactory
     * @param \ITvoice\Asn\Model\AsnFactory $asnFactory
     */
    public function __construct(
        SequenceCreator $sequenceCreator,
        \ITvoice\AsnVerification\Model\AsnFactoryItemFactory $asnFactoryItemFactory,
        \ITvoice\Asn\Model\AsnFactory $asnFactory
    )
    {
        $this->sequenceCreator = $sequenceCreator;
        $this->asnFactoryItemFactory = $asnFactoryItemFactory;
        $this->asnFactory = $asnFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $this->sequenceCreator->create();
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $this->setIsReleasedForCurrentExistingAsns();
        }

        $setup->endSetup();
    }

    /**
     *
     */
    protected function setIsReleasedForCurrentExistingAsns()
    {
        $currentAsnNumbers = [];
        $factoryItemCollection = $this->asnFactoryItemFactory->create()->getCollection();
        foreach ($factoryItemCollection as $factoryItem) {
            $asnNumber = $factoryItem->getAsnNumber();
            $currentAsnNumbers[$asnNumber] = $asnNumber;
        }

        $asnResource = $this->asnFactory->create()->getResource();

        $asnResource->getConnection()->update(
            $asnResource->getMainTable(),
            [
                'is_released' => 1
            ],
            [
                'asn_number in (?)' =>  $currentAsnNumbers
            ],
        );
    }
}
