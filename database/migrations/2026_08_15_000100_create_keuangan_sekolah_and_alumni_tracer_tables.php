<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pos_keuangan')) {
            Schema::create('pos_keuangan', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 30)->unique();
                $table->string('nama', 100);
                $table->enum('tipe', ['bulanan', 'bebas', 'sekali_bayar'])->default('bebas');
                $table->decimal('nominal_default', 15, 2)->default(0);
                $table->string('tahun_ajaran', 20)->nullable();
                $table->text('deskripsi')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tagihan_siswa')) {
            Schema::create('tagihan_siswa', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pos_keuangan_id')->constrained('pos_keuangan')->onDelete('cascade');
                $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
                $table->string('tahun_ajaran', 20)->nullable();
                $table->string('bulan', 20)->nullable(); // e.g. Juli, Agustus, etc. for bulanan
                $table->decimal('nominal', 15, 2)->default(0);
                $table->decimal('terbayar', 15, 2)->default(0);
                $table->decimal('sisa', 15, 2)->default(0);
                $table->enum('status', ['belum_bayar', 'cicilan', 'lunas'])->default('belum_bayar');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('transaksi_keuangan')) {
            Schema::create('transaksi_keuangan', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_transaksi', 50)->unique();
                $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
                $table->foreignId('pos_keuangan_id')->nullable()->constrained('pos_keuangan')->onDelete('set null');
                $table->foreignId('tagihan_siswa_id')->nullable()->constrained('tagihan_siswa')->onDelete('set null');
                $table->decimal('nominal_bayar', 15, 2)->default(0);
                $table->date('tanggal_bayar');
                $table->string('metode_pembayaran', 30)->default('Tunai');
                $table->string('keterangan')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }

        Schema::table('alumni', function (Blueprint $table) {
            if (!Schema::hasColumn('alumni', 'status_alumni')) {
                $table->string('status_alumni', 50)->nullable()->default('Belum Mengisi')->after('alamat');
            }
            if (!Schema::hasColumn('alumni', 'nama_instansi')) {
                $table->string('nama_instansi', 150)->nullable()->after('status_alumni');
            }
            if (!Schema::hasColumn('alumni', 'jurusan_posisi')) {
                $table->string('jurusan_posisi', 150)->nullable()->after('nama_instansi');
            }
            if (!Schema::hasColumn('alumni', 'tahun_mulai')) {
                $table->year('tahun_mulai')->nullable()->after('jurusan_posisi');
            }
            if (!Schema::hasColumn('alumni', 'keterangan_tracer')) {
                $table->text('keterangan_tracer')->nullable()->after('tahun_mulai');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_keuangan');
        Schema::dropIfExists('tagihan_siswa');
        Schema::dropIfExists('pos_keuangan');

        Schema::table('alumni', function (Blueprint $table) {
            $table->dropColumn([
                'status_alumni',
                'nama_instansi',
                'jurusan_posisi',
                'tahun_mulai',
                'keterangan_tracer',
            ]);
        });
    }
};
