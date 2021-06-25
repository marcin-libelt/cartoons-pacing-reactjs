<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Controller\Adminhtml\Asn;

/**
 * Class Index
 * @package ITvoice\AsnCreator\Controller\Adminhtml\Index
 */
class Edit extends \ITvoice\Asn\Controller\Adminhtml\Asn
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        try {
            $asn = $this->initAsn('asn_id');
        } catch (\Exception $exception) {
            $this->messageManager->addException($exception);
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('itvoice_asn/asn/view', ['id' => $this->getRequest()->getParam('asn_id')]);
            return $resultRedirect;
        }

        if (!$asn->canEdit()) {
            $this->messageManager->addError(__('This ASN cannot be edited.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('itvoice_asn/asn/view', ['id' => $asn->getId()]);
            return $resultRedirect;
        }

        $this->_view->loadLayout();
        $this->_setActiveMenu('Itvoice_Asn::asn');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Edit ASN %1', $asn->getAsnNumber()));
        $this->_view->renderLayout();
    }
}
