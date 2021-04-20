<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Model\Creator;

/**
 * Class Product
 * @package ITvoice\AsnCreator\Model\Creator
 */
class Product extends \Magento\Framework\Model\AbstractModel
{
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \ITvoice\AsnCreator\Model\ResourceModel\Creator\Product $resource,
        \ITvoice\AsnCreator\Model\ResourceModel\Creator\Product\Collection $resourceCollection
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );
    }
}
