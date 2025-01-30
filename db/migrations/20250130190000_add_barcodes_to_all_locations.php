<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class AddBarcodesToAllLocations extends AbstractMigration
{

    /**
     * Add barcodes to all locations, which is now enabled by default.
     * Previously they were only created if you clicked the icon next to the barcode - which created odd behaviour with assets
     */
    public function change(): void
    {
        $builder = $this->getQueryBuilder();
        $locationsQuery = $builder->select(['locations_id'])->from('locations')->execute();
        $locations = $locationsQuery->fetchAll();

        foreach ($locations as $location) {
            $builderLocationBarcode = $this->getQueryBuilder();
            $locationBarcodeQuery = $builderLocationBarcode->select(['locationsBarcodes_id'])->from('locationsBarcodes')->where(['locations_id' => $location[0]])->execute();
            $locationBarcode = $locationBarcodeQuery->fetchAll();
            if (count($locationBarcode) > 0)
                continue;

            echo "Adding barcode for location " . $location[0] . " \n";
            $locationBarcodeData = [
                "locationsBarcodes_value" => "L" . $location[0],
                "locationsBarcodes_type" => "QR_CODE",
                "locations_id" => $location[0],
                "locationsBarcodes_added" => date("Y-m-d H:i:s")
            ];

            $table = $this->table('locationsBarcodes');
            $table->insert($locationBarcodeData)
                ->saveData();
        }
    }
}
