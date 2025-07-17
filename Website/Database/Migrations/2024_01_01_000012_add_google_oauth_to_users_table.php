<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGoogleOauthToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique()->after('email');
            $table->string('provider')->nullable()->default('local')->after('google_id');
            $table->string('avatar')->nullable()->after('provider');
            $table->timestamp('email_verified_at')->nullable()->change();
            $table->string('password')->nullable()->change();
            $table->json('social_links')->nullable()->after('avatar');
            $table->boolean('newsletter_subscribed')->default(false)->after('social_links');
            $table->timestamp('last_login_at')->nullable()->after('newsletter_subscribed');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->json('preferences')->nullable()->after('last_login_ip');
            
            $table->index(['google_id']);
            $table->index(['provider']);
            $table->index(['last_login_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['google_id']);
            $table->dropIndex(['provider']);
            $table->dropIndex(['last_login_at']);
            
            $table->dropColumn([
                'google_id',
                'provider',
                'avatar',
                'social_links',
                'newsletter_subscribed',
                'last_login_at',
                'last_login_ip',
                'preferences'
            ]);
            
            $table->string('password')->nullable(false)->change();
        });
    }
}