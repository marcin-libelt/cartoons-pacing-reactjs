<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Model\Profile;

use ITvoice\Asn\Model\Asn;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ReleaseAnsProfile
 * @package ITvoice\AsnCreator\Model\Profile
 */
class ReleaseAsnProfile extends \Alekseon\Dataflows\Model\Profile implements \Alekseon\Dataflows\Model\ProfileInterface
{
    /**
     * @var \ITvoice\Asn\Model\AsnFactory
     */
    protected $asnFactory;
    /**
     * @var \ITvoice\Asn\Model\AsnCsv
     */
    protected $asnCsv;
    /**
     * @var \ITvoice\Ftp\Model\ConnectionFactory
     */
    protected $ftpConnectionFactory;
    /**
     * @var
     */
    protected $asnReleasedCounter = 0;

    /**
     * ReleaseAsnProfile constructor.
     * @param \Alekseon\Dataflows\Model\Profile\DataReaderFactory $dataReaderFactory
     * @param \ITvoice\Asn\Model\AsnFactory $asnFactory
     * @param \ITvoice\AsnCreator\Model\AsnCsv $asnCsv
     * @param \ITvoice\Ftp\Model\ConnectionFactory $ftpConnectionFactory
     */
    public function __construct(
        \Alekseon\Dataflows\Model\Profile\DataReaderFactory $dataReaderFactory,
        \ITvoice\Asn\Model\AsnFactory $asnFactory,
        \ITvoice\AsnCreator\Model\AsnCsv $asnCsv,
        \ITvoice\Ftp\Model\ConnectionFactory $ftpConnectionFactory
    ) {
        $this->asnFactory = $asnFactory;
        $this->asnCsv = $asnCsv;
        $this->ftpConnectionFactory = $ftpConnectionFactory;
        parent::__construct($dataReaderFactory);
    }

    /**
     * @return array[]
     */
    protected function getExportsConfiguration()
    {
        return [
            [
                'name' => 'Path1',
                'connection' => $this->ftpConnectionFactory->create(),
                'dir_path' => $this->getParam('dir_path_1'),
            ],
            [
                'name' => 'Path2',
                'connection' => $this->ftpConnectionFactory->create(),
                'dir_path' => $this->getParam('dir_path_2'),
            ],
            [
                'name' => 'Centric',
                'connection' => $this->getCentricFtpConnection(),
                'dir_path' => $this->getParam('centric_dir_path'),
            ],
        ];
    }

    /**
     *
     */
    protected function getAsnCollectionToRelease($exportNumber = null)
    {
        $collection = $this->asnFactory->create()->getCollection();
        $collection->addFieldToFilter('type', Asn::ASN_TYPE_INTERNAL);
        if ($exportNumber !== null) {
            $collection->addFieldToFilter('update_export_state', $exportNumber);
        }
        $collection->addFieldToFilter('release_status', Asn::RELEASE_STATUS_READY_TO_RELEASE);
        return $collection;
    }

    /**
     * @return bool
     */
    public function canBeExecuted()
    {
        $newAsnToRelease = $this->getAsnCollectionToRelease();
        if ($newAsnToRelease->getSize()) {
            return true;
        }

        return false;
    }

    /**
     *
     */
    public function execute()
    {
        $exportedAsns = [];
        $exportsConfiguration = $this->getExportsConfiguration();
        foreach ($exportsConfiguration as $exportNumber => $exportConfig) {
            $asnCollectionToRelease = $this->getAsnCollectionToRelease($exportNumber);

            try {
                $this->exportAsn($asnCollectionToRelease, $exportNumber);
            } catch (\Exception $e) {
                $this->addCriticalLog($e->getMessage());
                return;
            }

            $nextExportNumber = $exportNumber + 1;

            /** @var Asn $asn */
            foreach ($asnCollectionToRelease as $asn) {
                $exportedAsns[] = $asn;
                $save = false;
                if ($asn->getExportState() < $nextExportNumber) {
                    $asn->setExportState($nextExportNumber);
                    $save = true;
                }
                if ($asn->getUpdateExportState() < $nextExportNumber) {
                    $asn->setUpdateExportState($nextExportNumber);
                    $save = true;
                }
                if ($save) {
                    $asn->save();
                }
            }
        }

        /** @var Asn $asn */
        foreach ($exportedAsns as $asn) {
            $this->asnReleasedCounter++;
            if ($asn->getStatus() == ASN::STATUS_CANCELED) {
                $asn->setReleaseStatus(Asn::STATUS_CANCELED);
            } else {
                $asn->setIsReleased();
            }
            $asn->save();
            $this->addInfoLog('ASN ' . $asn->getAsnNumber() . ' has been released.');
        }

        $this->setResult($this->asnReleasedCounter . ' of ASNs has been released.');
    }

    /**
     * @param $asn
     */
    protected function exportAsn($asnCollection, $exportNumber)
    {
        $exportsConfiguration = $this->getExportsConfiguration();
        $exportConfig = $exportsConfiguration[$exportNumber];

        $delimiter = $this->getParam('delimiter');
        $enclosure = $this->getParam('enclosure');

        $dirPath = $exportConfig['dir_path'];
        $ftpConnection = $exportConfig['connection'];
        $name = $exportConfig['name'];
        $date = date("dmy_His");

        if (!$dirPath) {
            return;
        }

        $asnGroups = [
            'new' => [
                'asns' => [],
                'suffix' => '',
            ],
            'updated' => [
                'asns' => [],
                'suffix' => '_U',
            ],
        ];

        foreach ($asnCollection as $asn) {
            if ($asn->getExportState() > $exportNumber) {
                $asn->setOperand('U');
                $asnGroups['updated']['asns'][] = $asn;
            } else {
                if ($asn->getStatus() !== ASN::STATUS_CANCELED) {
                    $asnGroups['new']['asns'][] = $asn;
                }
            }
        }

        foreach ($asnGroups as $asnGroup) {
            $suffix = $asnGroup['suffix'];
            $asns = $asnGroup['asns'];
            if (empty($asns)) {
                continue;
            }

            $csvContent = $this->asnCsv->getCsv($asns, $delimiter, $enclosure);
            $filePath = $dirPath . DIRECTORY_SEPARATOR . 'TWSIN' . $suffix . '.' . $date . ".csv";

            try {
                $uploadResult = $ftpConnection->uploadFile($filePath, $csvContent);
            } catch (\Exception $e) {
                throw new LocalizedException(
                    __('Unable to upload file to %1, %2: %3', $name, $filePath, $e->getMessage())
                );
            }

            if ($uploadResult) {
                $this->addInfoLog('File Uploaded to ' . $name . ', ' . $filePath);
            } else {
                throw new LocalizedException(
                    __('Unable to upload file to %1, %2: %3', $name, $filePath, $ftpConnection->getLastError())
                );
            }
        }
    }

    /**
     * @return \ITvoice\Ftp\Model\Connection
     */
    protected function getCentricFtpConnection()
    {
        $connection = $this->ftpConnectionFactory->create();
        $host = $this->getParam('centric_ftp_host');
        $port = $this->getParam('centric_ftp_port');
        if ($port) {
            $host .= ':' . $port;
        }
        $user = $this->getParam('centric_ftp_user');
        $password = $this->getParam('centric_ftp_password');
        $connection->setHost($host);
        $connection->setUsername($user);
        $connection->setPassword($password);
        return $connection;
    }

    /**
     * @return \string[][]
     */
    public function getParametersFormConfig()
    {
        return [
            'ftp_connection' => [
                'type' => 'fieldset',
                'legend' => 'Ftp Connection',
            ],
            'centric_connection' => [
                'type' => 'fieldset',
                'legend' => 'Centric FTP Connection',
            ],
            'dir_path_1' => [
                'type' => 'text',
                'label' => 'Directory Path 1 to upload CSV File',
                'fieldset' => 'ftp_connection',
            ],
            'dir_path_2' => [
                'type' => 'text',
                'label' => 'Directory Path 2 to upload CSV File',
                'fieldset' => 'ftp_connection',
            ],
            'centric_ftp_host' => [
                'type' => 'text',
                'label' => 'Host',
                'fieldset' => 'centric_connection',
            ],
            'centric_ftp_port' => [
                'type' => 'text',
                'label' => 'Port',
                'fieldset' => 'centric_connection',
            ],
            'centric_ftp_user' => [
                'type' => 'text',
                'label' => 'User',
                'fieldset' => 'centric_connection',
            ],
            'centric_ftp_password' => [
                'type' => 'password',
                'label' => 'Password',
                'fieldset' => 'centric_connection',
            ],
            'centric_dir_path' => [
                'type' => 'text',
                'label' => 'Directory Path to upload CSV File For Centric',
                'fieldset' => 'centric_connection',
            ],
            'csv_options' => [
                'type' => 'fieldset',
                'legend' => 'CSV Export Options',
            ],
            'delimiter' => [
                'type' => 'text',
                'label' => ' field delimiter (one character only)',
                'fieldset' => 'csv_options',
            ],
            'enclosure' => [
                'type' => 'text',
                'label' => 'field enclosure (one character only)',
                'fieldset' => 'csv_options',
            ],
        ];
    }
}
