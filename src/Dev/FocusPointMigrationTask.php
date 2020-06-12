<?php

namespace JonoM\FocusPoint\Dev;


use SilverStripe\Assets\Image;
use SilverStripe\Dev\MigrationTask;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\Queries\SQLUpdate;
use SilverStripe\Versioned\Versioned;

class FocusPointMigrationTask extends MigrationTask
{
    private static $segment = 'FocusPointMigrationTask';
    protected $title = 'Migrate Focus-Point Field-Values.';
    protected $description = 'Migrate Focus-Point fields from v2 to v3 or vice-versa.';

    public function run($request)
    {
        if ($request && $request->getVar('direction') == 'down') {
            $this->down();
        } else {
            parent::run($request);
        }
    }

    // upgrade to new version
    public function up()
    {
        $this->changeDbFields('Focus', 'FocusPoint', 'Updated FocusPoint fields from v2 to v3');
    }

    // Revert to old version
    public function down()
    {
        $this->changeDbFields('FocusPoint', 'Focus', 'Downgraded FocusPoint fields from v3 to v2');
    }

    protected function changeDbFields($from, $to, $message)
    {
        $schema = DataObject::getSchema();
        $imageTable = $schema->tableName(Image::class);
        $fields = DB::field_list($imageTable);

        if (!isset($fields["{$from}X"])) {
            return;
        }

        // Safety net
        if (!isset($fields["{$to}X"]) || !isset($fields["{$to}Y"])) {
            throw new \Exception("$imageTable table does not have \"{$to}X\" and \"{$to}Y\" fields. Did you run dev/build?");
        }

        // Update all Image tables
        $imageTables = [
            $imageTable,
            $imageTable . "_" . Versioned::LIVE,
            $imageTable . "_Versions",
        ];

        DB::get_conn()->withTransaction(function() use ($imageTables, $from, $to, $message) {
            $oldColumnX = "\"{$from}X\"";
            $oldColumnY = "\"{$from}Y\"";
            $newColumnX = "\"{$to}X\"";
            $newColumnY = "\"{$to}Y\"";

            foreach ($imageTables as $imageTable) {
                $query = SQLUpdate::create("\"$imageTable\"")
                    ->assignSQL($newColumnX, $oldColumnX)
                    ->assignSQL($newColumnY, "$oldColumnY * -1");

                $query->execute();

                DB::query("ALTER TABLE \"$imageTable\" DROP COLUMN $oldColumnX");
                DB::query("ALTER TABLE \"$imageTable\" DROP COLUMN $oldColumnY");
            }

            DB::get_schema()->alterationMessage($message, 'changed');
        } , function () {
            DB::get_schema()->alterationMessage('Failed to alter FocusPoint fields', 'error');
        }, false, true);
    }
}
