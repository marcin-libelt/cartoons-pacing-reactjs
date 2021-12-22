<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Controller\Adminhtml\Asn;

use ITvoice\AsnCreator\Model\AsnCreator;
use Magento\Backend\App\Action\Context;

/**
 * Class Save
 * @package ITvoice\AsnCreator\Controller\Adminhtml\Asn
 */
class AutoSave extends \ITvoice\Asn\Controller\Adminhtml\Asn
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
        \Magento\Framework\Registry $registry,
        \ITvoice\Asn\Model\AsnRepository $asnRepository,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \ITvoice\AsnCreator\Model\AsnCreatorFactory $asnCreatorFactory
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->asnCreatorFactory = $asnCreatorFactory;
        parent::__construct($context, $registry, $asnRepository);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_forward('denied');
        }

        sleep(4);

        $jsonResponse = $this->jsonFactory->create();
        $data = [
            'status' => 1
        ];

        $jsonResponse->setData($data);
        return $jsonResponse;
    }
}
