<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Setup;

use Alekseon\Dataflows\Model\Schedule;
use ITvoice\Asn\Model\Asn;
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
     * @var \Alekseon\Dataflows\Setup\DataflowSetupFactory
     */
    protected $dataflowSetupFactory;

    /**
     * UpgradeData constructor.
     * @param SequenceCreator $sequenceCreator
     * @param \ITvoice\AsnVerification\Model\AsnFactoryItemFactory $asnFactoryItemFactory
     * @param \ITvoice\Asn\Model\AsnFactory $asnFactory
     */
    public function __construct(
        SequenceCreator $sequenceCreator,
        \ITvoice\AsnVerification\Model\AsnFactoryItemFactory $asnFactoryItemFactory,
        \ITvoice\Asn\Model\AsnFactory $asnFactory,
        \Alekseon\Dataflows\Setup\DataflowSetupFactory $dataflowSetupFactory
    )
    {
        $this->sequenceCreator = $sequenceCreator;
        $this->asnFactoryItemFactory = $asnFactoryItemFactory;
        $this->asnFactory = $asnFactory;
        $this->dataflowSetupFactory = $dataflowSetupFactory;
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

        //if (version_compare($context->getVersion(), '1.0.2') < 0) {
        //}

        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $this->createReleaseAsnProfile($setup);
        }

        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $this->setReleaseStatusForCurrentExistingAsns();
        }

        $setup->endSetup();
    }

    /**
     *
     */
    protected function setReleaseStatusForCurrentExistingAsns()
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
                'release_status' => Asn::RELEASED_FLAG_RELEASED
            ],
            [
                'asn_number in (?)' =>  $currentAsnNumbers
            ],
        );
    }

    /**
     * @param $setup
     */
    protected function createReleaseAsnProfile($setup)
    {
        $dataflowsSetup = $this->dataflowSetupFactory->create(['setup' => $setup]);
        $dataflowsSetup->createSchedule(
            'release_asn',
            [
                'name' => 'Release Asn',
                'status' => Schedule::STATUS_DISABLED,
                'profile_class' => 'ITvoice\AsnCreator\Model\Profile\ReleaseAsnProfile',
                'schedule' => '*/5 * * * *',
                'parameters' => [
                    'dir_path_1' => '/INT18/TWS/IMPORT/INBOUND',
                    'dir_path_2' => '/INT18/ITVOICE/INBOUND_ORIGINAL',
                    'delimiter' => '|',
                    'enclosure' => '"',
                ],
            ]
        );
    }
}
