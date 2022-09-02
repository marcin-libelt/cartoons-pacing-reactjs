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
            'FactoryInvoiceNumber',
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
            'Ordertype',
            'Cites',
            'FishWildlife'
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
                            'factory_invoice_number' => $asn->getPackingListNumber(),
                            'uci' => $carton->getUniqueCartonId(true),
                            'barcode' => $simpleItem->getBarcode(),
                            'qty' => $simpleItem->getQty(),
                            'supplier_unit_cost_price' => sprintf('%.2f', $poItem->getUnitSellingPrice()),
                            'joor_so_number' => $carton->getJoorSoNumber(),
                            'so_number' => $carton->getJoorSoNumber() . '-' . $poItem->getShippingDoorCode(),
                            'po_number' => $carton->getMbpo(),
                            'customer_po' => '', //@TODO how to get it , is it always empty ?
                            'carton_gross_weight' => (float)$carton->getGrossWeight(),
                            'carton_new_weight' => (float)$carton->getNetWeight(),
                            'operand' => 'I',
                            'order_type' => 'NEW',
                            'cites' => $carton->getCites(),
                            'fish_wild_life' => $carton->getFishWildLife(),
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
