<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Model\ResourceModel\Creator\Carton;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package ITvoice\AsnCreator\Model\ResourceModel\Creator\Carton
 */
class Collection extends AbstractCollection
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->authSession = $authSession;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'ITvoice\AsnCreator\Model\Creator\Carton',
            'ITvoice\AsnCreator\Model\ResourceModel\Creator\Carton'
        );
    }

    /**
     * @return mixed
     */
    protected function getUserId()
    {
        return $this->authSession->getUser()->getId();
    }

    /**
     * @param $user
     */
    public function clear()
    {
        $userId = (int) $this->getUserId();
        if ($userId) {
            $where = ['user_id = ?' => $userId];
            $connection = $this->getResource()->getConnection();
            $connection->delete($this->_mainTable, $where);
        }
        return $this;
    }
}
