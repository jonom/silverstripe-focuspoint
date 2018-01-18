<?php

namespace JonoM\FocusPoint\Dev;


use SilverStripe\Dev\MigrationTask;

class FocusPointMigrationTask extends MigrationTask
{
    // upgrade to new version
    public function up()
    {
        //TODO: Check if FocusX,Y field still exists, if not, exit.
        //TODO: Copy field values from FocusX,Y over to FocusPointFocusX,Y
        //TODO: Delete FocusX,Y fields
    }

    // Revert to old version
    public function down()
    {
        //TODO: Check if FocusPointFocusX,Y exists. If not, exit
        //TODO: Copy field values from FocusPointFocusX,Y over to FocusX,Y
        //TODO: Delete FocusPointFocusX,Y fields
    }
}
