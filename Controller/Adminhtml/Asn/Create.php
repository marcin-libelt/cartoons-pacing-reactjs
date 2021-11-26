<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Controller\Adminhtml\Asn;

/**
 * Class Create
 * @package ITvoice\AsnCreator\Controller\Adminhtml\Index
 */
class Create extends \Magento\Backend\App\Action
{
    /**
     * @var \ITvoice\Factory\Model\FactoryRepository
     */
    protected $factoryRepository;
    /**
     * @var \ITvoice\AsnCreator\Model\AsnCreatorFactory
     */
    protected $asnCreatorFactory;
    /**
     * @var \ITvoice\AsnCreator\Model\Creator
     */
    protected $creator;

    /**
     * Factory constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \ITvoice\Factory\Model\FactoryRepository $factoryRepository
     * @param \ITvoice\AsnCreator\Model\AsnCreatorFactory $asnCreatorFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \ITvoice\Factory\Model\FactoryRepository $factoryRepository,
        \ITvoice\AsnCreator\Model\AsnCreatorFactory $asnCreatorFactory
    ) {
        $this->factoryRepository = $factoryRepository;
        $this->asnCreatorFactory = $asnCreatorFactory;
        parent::__construct($context);
    }


    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $factoryId = $this->getRequest()->getParam('factory_id');
        $factory = $this->factoryRepository->getByEntityId($factoryId);

        if ($factory) {
            $asnCreator = $this->asnCreatorFactory->create();
            $asnCreator->setFactoryId($factoryId);
            $asn = $asnCreator->create();
            return $this->resultRedirectFactory->create()->setPath('*/asn/edit', ['asn_id' => $asn->getId()]);
        } else {
            return $this->resultRedirectFactory->create()->setPath('*/*');
        }
    }
}
