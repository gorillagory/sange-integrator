<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('service_records')) {
            return;
        }

        Schema::table('service_records', function (Blueprint $table) {
            if (! Schema::hasColumn('service_records', 'service_status')) {
                $table->string('service_status', 50)->default('Pending')->after('status');
            }
        });

        if (Schema::hasTable('service_records') && Schema::hasColumn('service_records', 'service_status')) {
            DB::connection('tenant')
                ->table('service_records')
                ->whereNull('service_status')
                ->update(['service_status' => 'Pending']);
        }

        if (! Schema::hasTable('service_record_documents')) {
            Schema::create('service_record_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('service_record_id');
                $table->unsignedBigInteger('company_id');
                $table->unsignedBigInteger('template_id')->nullable();
                $table->string('document_type', 50);
                $table->string('document_number', 255);
                $table->string('template_name')->nullable();
                $table->string('template_code')->nullable();
                $table->string('status', 50)->default('Generated');
                $table->unsignedBigInteger('generated_by')->nullable();
                $table->timestamp('generated_at')->nullable();
                $table->timestamp('last_downloaded_at')->nullable();
                $table->timestamps();

                $table->index(['service_record_id', 'document_type']);
                $table->index(['company_id', 'document_type']);
                $table->index('document_number');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('service_record_documents')) {
            Schema::drop('service_record_documents');
        }

        if (! Schema::hasTable('service_records')) {
            return;
        }

        Schema::table('service_records', function (Blueprint $table) {
            if (Schema::hasColumn('service_records', 'service_status')) {
                $table->dropColumn('service_status');
            }
        });
    }
};
