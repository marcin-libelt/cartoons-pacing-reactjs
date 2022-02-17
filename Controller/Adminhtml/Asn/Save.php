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
class Save extends \ITvoice\Asn\Controller\Adminhtml\Asn
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


        $jsonResponse = $this->jsonFactory->create();
        /** @var AsnCreator $asnCreator */
        $asnCreator = $this->asnCreatorFactory->create();

        try {
            $asn = $this->initAsn('asn_id');
            $asnCreator->setAsn($asn);
            $asnCreator->setCartonsData($this->getRequest()->getParam('cartons'));
            $asnCreator->setPackingListDate($this->getRequest()->getParam('packing_list_date'));
            $asnCreator->setPackingListNumber($this->getRequest()->getParam('packing_list_number'));
            $asn = $asnCreator->create();

            $cartons = $asn->getAllCartons();
            $cartonData = [];
            foreach ($cartons as $carton) {
                if ($carton->getInitCartonId()) {
                    $cartonData[$carton->getInitCartonId()] = [
                        'unique_carton_id' => $carton->getUniqueCartonId()
                    ];
                }
            }

            $data = [
                'message' => __('Asn %1 has been saved.', $asn->getAsnNumber()),
                'status' => 1,
                'cartonsData' => $cartonData,
            ];
        } catch (\Exception $e) {
            $data = [
                'message' => $e->getMessage(),
                'status' => 9
            ];
            $jsonResponse->setStatusHeader(500, null, $e->getMessage());
        }

        $jsonResponse->setData($data);
        return $jsonResponse;
    }
}
