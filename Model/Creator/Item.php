<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Model\Creator;

/**
 * Class Item
 * @package ITvoice\AsnCreator\Model\Creator
 */
class Item extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Item constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \ITvoice\AsnCreator\Model\ResourceModel\Creator\Item $resource
     * @param \ITvoice\AsnCreator\Model\ResourceModel\Creator\Item\Collection $resourceCollection
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \ITvoice\AsnCreator\Model\ResourceModel\Creator\Item $resource,
        \ITvoice\AsnCreator\Model\ResourceModel\Creator\Item\Collection $resourceCollection
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );
    }
}
