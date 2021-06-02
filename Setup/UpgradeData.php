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
     * UpgradeData constructor.
     * @param SequenceCreator $sequenceCreator
     */
    public function __construct(
        SequenceCreator $sequenceCreator
    )
    {
        $this->sequenceCreator = $sequenceCreator;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $this->sequenceCreator->create();
        }

        $setup->endSetup();
    }
}
