<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class AddLabelPrinterSystem extends AbstractMigration
{
    /**
     * Add label printer system tables and seed default templates
     */
    public function change(): void
    {
        // Create labelPrinters table
        $this->table('labelPrinters', [
                'id' => false,
                'primary_key' => ['labelPrinters_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => 'Label printer definitions per instance',
            ])
            ->addColumn('labelPrinters_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('instances_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
            ])
            ->addColumn('labelPrinters_name', 'string', [
                'null' => false,
                'limit' => 255,
            ])
            ->addColumn('labelPrinters_manufacturer', 'enum', [
                'null' => false,
                'default' => 'brother',
                'values' => ['brother', 'zebra', 'dymo', 'generic'],
            ])
            ->addColumn('labelPrinters_model', 'string', [
                'null' => true,
                'limit' => 100,
            ])
            ->addColumn('labelPrinters_cupsName', 'string', [
                'null' => true,
                'limit' => 255,
                'comment' => 'CUPS printer queue name',
            ])
            ->addColumn('labelPrinters_cupsServer', 'string', [
                'null' => true,
                'limit' => 255,
                'comment' => 'CUPS server address (e.g., raspberrypi.local:631)',
            ])
            ->addColumn('labelPrinters_capabilities', 'json', [
                'null' => true,
                'comment' => 'Supported label sizes, resolutions, features',
            ])
            ->addColumn('labelPrinters_deleted', 'boolean', [
                'null' => false,
                'default' => 0,
            ])
            ->addColumn('labelPrinters_created', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->addColumn('labelPrinters_updated', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
            ])
            ->addIndex(['instances_id', 'labelPrinters_deleted'], [
                'name' => 'idx_instance',
            ])
            ->addForeignKey('instances_id', 'instances', 'instances_id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->create();

        // Create labelTemplates table
        $this->table('labelTemplates', [
                'id' => false,
                'primary_key' => ['labelTemplates_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => 'Label layout templates per instance',
            ])
            ->addColumn('labelTemplates_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('instances_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
            ])
            ->addColumn('labelTemplates_name', 'string', [
                'null' => false,
                'limit' => 255,
            ])
            ->addColumn('labelTemplates_description', 'text', [
                'null' => true,
            ])
            ->addColumn('labelTemplates_manufacturer', 'enum', [
                'null' => false,
                'default' => 'brother',
                'values' => ['brother', 'zebra', 'dymo', 'generic'],
            ])
            ->addColumn('labelTemplates_width', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'Width in mm',
            ])
            ->addColumn('labelTemplates_height', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'Height in mm (0 for continuous)',
            ])
            ->addColumn('labelTemplates_orientation', 'enum', [
                'null' => false,
                'default' => 'horizontal',
                'values' => ['horizontal', 'vertical'],
            ])
            ->addColumn('labelTemplates_template', 'json', [
                'null' => false,
                'comment' => 'Template definition (fields, layout, styling)',
            ])
            ->addColumn('labelTemplates_deleted', 'boolean', [
                'null' => false,
                'default' => 0,
            ])
            ->addColumn('labelTemplates_created', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->addColumn('labelTemplates_updated', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
            ])
            ->addIndex(['instances_id', 'labelTemplates_deleted'], [
                'name' => 'idx_instance',
            ])
            ->addForeignKey('instances_id', 'instances', 'instances_id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->create();

        // Create labelPrinterTemplates table
        $this->table('labelPrinterTemplates', [
                'id' => false,
                'primary_key' => ['labelPrinterTemplates_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => 'Printer-template associations',
            ])
            ->addColumn('labelPrinterTemplates_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('labelPrinters_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
            ])
            ->addColumn('labelTemplates_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
            ])
            ->addColumn('labelPrinterTemplates_isDefault', 'boolean', [
                'null' => false,
                'default' => 0,
            ])
            ->addColumn('labelPrinterTemplates_deleted', 'boolean', [
                'null' => false,
                'default' => 0,
            ])
            ->addIndex(['labelPrinters_id', 'labelTemplates_id'], [
                'name' => 'unique_printer_template',
                'unique' => true,
            ])
            ->addIndex(['labelPrinters_id', 'labelPrinterTemplates_isDefault'], [
                'name' => 'idx_default',
            ])
            ->addForeignKey('labelPrinters_id', 'labelPrinters', 'labelPrinters_id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('labelTemplates_id', 'labelTemplates', 'labelTemplates_id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->create();

        // Create labelPrintJobs table
        $this->table('labelPrintJobs', [
                'id' => false,
                'primary_key' => ['labelPrintJobs_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => 'Print job audit log',
            ])
            ->addColumn('labelPrintJobs_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('instances_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
            ])
            ->addColumn('labelPrinters_id', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
            ])
            ->addColumn('labelTemplates_id', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
            ])
            ->addColumn('assets_id', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
            ])
            ->addColumn('users_userid', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
            ])
            ->addColumn('labelPrintJobs_status', 'enum', [
                'null' => false,
                'default' => 'pending',
                'values' => ['pending', 'sent', 'success', 'failed'],
            ])
            ->addColumn('labelPrintJobs_data', 'json', [
                'null' => true,
                'comment' => 'Data used to render label',
            ])
            ->addColumn('labelPrintJobs_error', 'text', [
                'null' => true,
            ])
            ->addColumn('labelPrintJobs_timestamp', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->addIndex(['instances_id', 'labelPrintJobs_timestamp'], [
                'name' => 'idx_instance',
            ])
            ->addIndex(['labelPrintJobs_status', 'labelPrintJobs_timestamp'], [
                'name' => 'idx_status',
            ])
            ->addForeignKey('instances_id', 'instances', 'instances_id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('labelPrinters_id', 'labelPrinters', 'labelPrinters_id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('labelTemplates_id', 'labelTemplates', 'labelTemplates_id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('assets_id', 'assets', 'assets_id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('users_userid', 'users', 'users_userid', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->create();

        // Seed default templates for each instance
        echo "Seeding default label templates for all instances...\n";

        $builder = $this->getQueryBuilder();
        $instancesQuery = $builder->select(['instances_id'])->from('instances')->execute();
        $instances = $instancesQuery->fetchAll();

        foreach ($instances as $instance) {
            $instanceId = $instance[0];
            echo "Adding default templates for instance $instanceId\n";

            // Template 1: 24mm Square (UPC-E + QR Code)
            $template1 = [
                'instances_id' => $instanceId,
                'labelTemplates_name' => '24mm Square - Full Info',
                'labelTemplates_description' => 'Square label with barcode and QR code for larger items',
                'labelTemplates_manufacturer' => 'brother',
                'labelTemplates_width' => 24,
                'labelTemplates_height' => 24,
                'labelTemplates_orientation' => 'horizontal',
                'labelTemplates_template' => json_encode([
                    'elements' => [
                        [
                            'type' => 'text',
                            'x' => 1,
                            'y' => 1,
                            'field' => 'asset_tag',
                            'font' => 'Arial',
                            'size' => 10,
                            'bold' => true,
                            'align' => 'left'
                        ],
                        [
                            'type' => 'barcode',
                            'x' => 1,
                            'y' => 6,
                            'field' => 'barcode_value',
                            'format' => 'UPC_E',
                            'width' => 22,
                            'height' => 8
                        ],
                        [
                            'type' => 'qrcode',
                            'x' => 1,
                            'y' => 16,
                            'field' => 'qr_url',
                            'size' => 6
                        ],
                        [
                            'type' => 'text',
                            'x' => 8,
                            'y' => 16,
                            'field' => 'instance_name',
                            'font' => 'Arial',
                            'size' => 6,
                            'align' => 'left'
                        ]
                    ]
                ])
            ];

            // Template 2: 24mm x 40mm (Cable Wrap-Around)
            $template2 = [
                'instances_id' => $instanceId,
                'labelTemplates_name' => '24mm Cable Label',
                'labelTemplates_description' => 'Rectangular label for wrapping around cables',
                'labelTemplates_manufacturer' => 'brother',
                'labelTemplates_width' => 24,
                'labelTemplates_height' => 40,
                'labelTemplates_orientation' => 'horizontal',
                'labelTemplates_template' => json_encode([
                    'elements' => [
                        [
                            'type' => 'text',
                            'x' => 1,
                            'y' => 1,
                            'field' => 'asset_tag',
                            'font' => 'Arial',
                            'size' => 12,
                            'bold' => true
                        ],
                        [
                            'type' => 'barcode',
                            'x' => 1,
                            'y' => 8,
                            'field' => 'barcode_value',
                            'format' => 'UPC_E',
                            'width' => 50,
                            'height' => 14
                        ]
                    ]
                ])
            ];

            // Template 3: 12mm x 24mm (Small Items)
            $template3 = [
                'instances_id' => $instanceId,
                'labelTemplates_name' => '12mm Small Label',
                'labelTemplates_description' => 'Compact horizontal label for smaller items',
                'labelTemplates_manufacturer' => 'brother',
                'labelTemplates_width' => 12,
                'labelTemplates_height' => 24,
                'labelTemplates_orientation' => 'horizontal',
                'labelTemplates_template' => json_encode([
                    'elements' => [
                        [
                            'type' => 'barcode',
                            'x' => 0,
                            'y' => 0,
                            'field' => 'barcode_value',
                            'format' => 'UPC_E',
                            'width' => 40,
                            'height' => 10
                        ],
                        [
                            'type' => 'text',
                            'x' => 42,
                            'y' => 2,
                            'field' => 'asset_tag',
                            'font' => 'Arial',
                            'size' => 8
                        ]
                    ]
                ])
            ];

            $table = $this->table('labelTemplates');
            $table->insert($template1)->saveData();
            $table->insert($template2)->saveData();
            $table->insert($template3)->saveData();
        }

        // Grant label printer permissions to admin-like positions
        echo "Granting label printer permissions to admin positions...\n";

        $newPermissions = [
            'ASSETS:LABEL_PRINTERS:VIEW',
            'ASSETS:LABEL_PRINTERS:PRINT',
            'ASSETS:LABEL_PRINTERS:EDIT',
            'ASSETS:LABEL_PRINTERS:CREATE',
            'ASSETS:LABEL_PRINTERS:DELETE'
        ];

        // Get all positions that have BUSINESS:BUSINESS_SETTINGS:VIEW (admin-like positions)
        $positionsQuery = $builder->select(['instancePositions_id', 'instancePositions_actions'])
            ->from('instancePositions')
            ->where(['instancePositions_actions LIKE' => '%BUSINESS:BUSINESS_SETTINGS:VIEW%'])
            ->execute();

        $positions = $positionsQuery->fetchAll();

        foreach ($positions as $position) {
            $positionId = $position[0];
            $currentActions = $position[1];

            if (!$currentActions) {
                continue;
            }

            $actionsArray = explode(',', $currentActions);

            // Add new permissions if they don't already exist
            foreach ($newPermissions as $permission) {
                if (!in_array($permission, $actionsArray)) {
                    $actionsArray[] = $permission;
                }
            }

            $updatedActions = implode(',', $actionsArray);

            // Update the position
            $this->execute(
                "UPDATE instancePositions SET instancePositions_actions = :actions WHERE instancePositions_id = :id",
                ['actions' => $updatedActions, 'id' => $positionId]
            );

            echo "  Granted permissions to position ID $positionId\n";
        }

        echo "Label printer system setup complete!\n";
    }
}