<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Model;

use ITvoice\Asn\Model\Asn;
use ITvoice\Asn\Model\ResourceModel\Asn\Collection;

/**
 * Class AsnCsv
 * @package ITvoice\AsnCreator\Model
 */
class AsnCsv
{
    /**
     * @param Collection $asnCollection
     * @param string $delimiter
     * @param string $enclosure
     * @return string
     */
    public function getCsv(Collection $asnCollection, $delimiter = ",", $enclosure = '"')
    {
        $csvContent = [];

        $header = [
            'ASNNumber',
            'WarehouseLocation',
            'BillingDoorCode',
            'ShippingDoorCode',
            'FactoryName',
            'FactoryPacklingListNumber',
            'UCI',
            'Barcode',
            'Qty',
            'SupplierUnitCostPrice',
            'JoorSONumber',
            'SONumber',
            'PONumber',
            'CustomerPO',
            'CartonGrossWeight',
            'CartonNetWeight',
            'operand',
            'InvoiceNumber',
            'InvoiceAmount',
            'InvoiceDate',
        ];

        $csvContent[] = $this->getCsvLine($header, $delimiter, $enclosure);;

        foreach ($asnCollection as $asn) {
            foreach ($asn->getAllCartons() as $carton) {
                foreach ($carton->getAllItems() as $item) {
                    foreach ($item->getAllSimpleItems() as $simpleItem) {
                        $poItem = $simpleItem->getPoItem();

                        $row = [
                            'asn_number' => $asn->getAsnNumber(),
                            'warehouse_location' => $item->getWarehouseLocation(),
                            'billing_door_code' => $poItem->getBillingDoorCode(),
                            'shipping_door_code' => $poItem->getShippingDoorCode(),
                            'factory_name' => $asn->getFactory(),
                            'factory_packing_list_number' => '', //@TODO how to get it ?
                            'uci' => $carton->getUniqueCartonId(),
                            'barcode' => $simpleItem->getBarcode(),
                            'qty' => $simpleItem->getQty(),
                            'supplier_unit_cost_price' => sprintf('%.2f', $poItem->getUnitSellingPrice()),
                            'joor_so_number' => $carton->getJoorSoNumber(),
                            'so_number' => '', //@TODO how to get it ?
                            'po_number' => $carton->getCustomerPo(),
                            'customer_po' => '', //@TODO how to get it , is it always empty ?
                            'carton_gross_weight' => (float)$carton->getGrossWeight(),
                            'carton_new_weight' => (float)$carton->getNetWeight(),
                            'operand' => 'I',
                            'invoice_number' => $asn->getInvoiceNumber(),
                            'invoice_amount' => sprintf('%.2f', $asn->getInvoiceAmount()),
                            'invoice_date' => '', //@TODO how to get it ?
                        ];

                        $csvContent[] = $this->getCsvLine($row, $delimiter, $enclosure);
                    }
                }
            }
        }

        $csvContent = implode("", $csvContent);

        return $csvContent;
    }

    /**
     * @param array $fields
     * @param string $delimiter
     * @param string $enclosure
     * @return string
     */
    protected function getCsvLine(array $fields, $delimiter = ",", $enclosure = '"') : string
    {
        $f = fopen('php://memory', 'r+');
        if (fputcsv($f, $fields, $delimiter, $enclosure) === false) {
            return false;
        }
        rewind($f);
        return stream_get_contents($f);
    }
}
