<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGmailColumnsToEmailLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sm_email_sms_logs', function (Blueprint $table) {
            $table->string('gmail_message_id')->nullable()->after('description');
            $table->string('gmail_thread_id')->nullable()->after('gmail_message_id');
            $table->enum('delivery_status', ['sent', 'delivered', 'read', 'bounced', 'failed'])
                  ->default('sent')
                  ->after('gmail_thread_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sm_email_sms_logs', function (Blueprint $table) {
            $table->dropColumn(['gmail_message_id', 'gmail_thread_id', 'delivery_status']);
        });
    }
}