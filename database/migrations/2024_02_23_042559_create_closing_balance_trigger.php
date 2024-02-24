<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // Trigger for INSERT
        DB::unprepared('
            CREATE TRIGGER update_closing_balance_insert
            BEFORE INSERT ON stocks
            FOR EACH ROW
            BEGIN
                SET NEW.closing_balance = NEW.opening_balance + NEW.receipt_qty - NEW.issue_qty;
            END;
        ');

        // Trigger for UPDATE
        DB::unprepared('
            CREATE TRIGGER update_closing_balance_update
            BEFORE UPDATE ON stocks
            FOR EACH ROW
            BEGIN
                SET NEW.closing_balance = NEW.opening_balance + NEW.receipt_qty - NEW.issue_qty;
            END;
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS update_closing_balance_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS update_closing_balance_update');
    }
};
