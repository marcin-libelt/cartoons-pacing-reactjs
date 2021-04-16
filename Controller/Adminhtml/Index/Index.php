<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Controller\Adminhtml\Index;

/**
 * Class Index
 * @package ITvoice\AsnCreator\Controller\Adminhtml\Index
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Itvoice_Asn::asn');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('ASN Creator'));
        $this->_view->renderLayout();
    }
}
