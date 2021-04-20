<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Model\Creator;

/**
 * Class Carton
 * @package ITvoice\AsnCreator\Model\Creator
 */
class Carton extends \Magento\Framework\Model\AbstractModel
{
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \ITvoice\AsnCreator\Model\ResourceModel\Creator\Carton $resource,
        \ITvoice\AsnCreator\Model\ResourceModel\Creator\Carton\Collection $resourceCollection
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );
    }
}
