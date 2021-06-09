<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;

/**
 * Class Create
 * @package ITvoice\AsnCreator\Controller\Adminhtml\Factory
 */
class Create extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;
    /**
     * @var \ITvoice\AsnCreator\Model\AsnCreatorFactory
     */
    protected $asnCreatorFactory;

    /**
     * GetItems constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \ITvoice\AsnCreator\Model\AsnCreatorFactory $asnCreatorFactory
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->asnCreatorFactory = $asnCreatorFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_forward('denied');
        }

        $jsonResponse = $this->jsonFactory->create();
        $asnCreator = $this->asnCreatorFactory->create();

        try {
            $asnCreator->setFactoryId($this->getRequest()->getParam('factory_id'));
            $asnCreator->setCartonsData($this->getRequest()->getParam('cartons'));
            $asn = $asnCreator->create();
            $data = [
                'message' => __('Asn %1 has been created.', $asn->getAsnNumber())
            ];
        } catch (\Exception $e) {
            $data = [
                'message' => $e->getMessage()
            ];
            $jsonResponse->setStatusHeader(500, null, $e->getMessage());
        }

        $jsonResponse->setData($data);
        return $jsonResponse;
    }
}
