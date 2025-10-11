<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('phone_validation_presets', function (Blueprint $table) {
            $table->id();
            $table->string('country_iso', 2)->unique();
            $table->string('country_name');
            $table->string('country_code', 5);
            $table->integer('national_length');
            $table->integer('min_length')->nullable();
            $table->integer('max_length')->nullable();
            $table->json('mobile_prefixes');
            $table->json('fixed_prefixes')->nullable();
            $table->json('premium_prefixes')->nullable();
            $table->json('operators')->nullable();
            $table->text('validation_regex')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $this->insertPresets();
    }

    /**
     * Insert phone validation presets for 9 countries
     */
    private function insertPresets(): void
    {
        $presets = [
            [
                'country_iso' => 'CI',
                'country_name' => 'Côte d\'Ivoire',
                'country_code' => '+225',
                'national_length' => 10,
                'min_length' => 10,
                'max_length' => 10,
                'mobile_prefixes' => json_encode(['01', '02', '03', '05', '06', '07', '08', '09']),
                'fixed_prefixes' => json_encode(['20', '21', '22', '23', '24', '30', '31', '32', '33', '34', '35', '36', '37']),
                'premium_prefixes' => json_encode(['44', '45', '46', '47', '48', '49']),
                'operators' => json_encode([
                    'Orange' => ['07', '08', '09'],
                    'MTN' => ['05', '06'],
                    'Moov' => ['01', '02', '03']
                ]),
                'validation_regex' => '^(01|02|03|05|06|07|08|09)[0-9]{8}$',
                'notes' => 'Mobile: 10 digits starting with 01-09. Fixed: 20-37.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_iso' => 'GH',
                'country_name' => 'Ghana',
                'country_code' => '+233',
                'national_length' => 9,
                'min_length' => 9,
                'max_length' => 9,
                'mobile_prefixes' => json_encode(['20', '23', '24', '25', '26', '27', '28', '50', '54', '55', '56', '57', '59']),
                'fixed_prefixes' => json_encode(['21', '22', '31', '32', '33', '34', '35', '36', '37', '38', '39', '41', '42', '43', '51', '52', '53']),
                'premium_prefixes' => json_encode(['44', '45', '46', '47', '48', '49']),
                'operators' => json_encode([
                    'MTN' => ['24', '54', '55', '59'],
                    'Vodafone' => ['20', '26', '50', '56'],
                    'AirtelTigo' => ['23', '25', '27', '28', '57']
                ]),
                'validation_regex' => '^(20|23|24|25|26|27|28|50|54|55|56|57|59)[0-9]{7}$',
                'notes' => 'Mobile: 9 digits starting with mobile prefixes.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_iso' => 'NG',
                'country_name' => 'Nigeria',
                'country_code' => '+234',
                'national_length' => 10,
                'min_length' => 10,
                'max_length' => 10,
                'mobile_prefixes' => json_encode(['70', '80', '81', '90', '91', '92', '93', '94', '95', '96', '97', '98', '99']),
                'fixed_prefixes' => json_encode(['01', '02', '03', '04', '05', '06', '07', '08', '09']),
                'premium_prefixes' => json_encode(['70', '80']),
                'operators' => json_encode([
                    'MTN' => ['80', '81', '90', '91', '92', '93', '94', '95', '96', '97', '98', '99'],
                    'Airtel' => ['70', '80', '81', '90', '91', '92', '93', '94', '95', '96', '97', '98', '99'],
                    'Glo' => ['70', '80', '81', '90', '91', '92', '93', '94', '95', '96', '97', '98', '99'],
                    '9mobile' => ['80', '81', '90', '91', '92', '93', '94', '95', '96', '97', '98', '99']
                ]),
                'validation_regex' => '^(70|80|81|90|91|92|93|94|95|96|97|98|99)[0-9]{8}$',
                'notes' => 'Mobile: 10 digits starting with 70, 80-81, 90-99.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_iso' => 'SN',
                'country_name' => 'Sénégal',
                'country_code' => '+221',
                'national_length' => 9,
                'min_length' => 9,
                'max_length' => 9,
                'mobile_prefixes' => json_encode(['70', '76', '77', '78']),
                'fixed_prefixes' => json_encode(['33', '38', '39', '40', '41', '42', '43', '44', '45', '46', '47', '48', '49']),
                'premium_prefixes' => json_encode(['70']),
                'operators' => json_encode([
                    'Orange' => ['77', '78'],
                    'Free' => ['76'],
                    'Expresso' => ['70']
                ]),
                'validation_regex' => '^(70|76|77|78)[0-9]{7}$',
                'notes' => 'Mobile: 9 digits starting with 70, 76-78.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_iso' => 'ML',
                'country_name' => 'Mali',
                'country_code' => '+223',
                'national_length' => 8,
                'min_length' => 8,
                'max_length' => 8,
                'mobile_prefixes' => json_encode(['60', '61', '62', '63', '64', '65', '66', '67', '68', '69', '70', '71', '72', '73', '74', '75', '76', '77', '78', '79']),
                'fixed_prefixes' => json_encode(['20', '21', '22', '23', '24', '25', '26', '27', '28', '29']),
                'premium_prefixes' => json_encode(['60', '61', '62', '63', '64']),
                'operators' => json_encode([
                    'Orange' => ['60', '61', '62', '63', '64', '65', '66', '67', '68', '69'],
                    'Malitel' => ['70', '71', '72', '73', '74', '75', '76', '77', '78', '79']
                ]),
                'validation_regex' => '^(6|7)[0-9]{7}$',
                'notes' => 'Mobile: 8 digits starting with 6 or 7.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_iso' => 'BF',
                'country_name' => 'Burkina Faso',
                'country_code' => '+226',
                'national_length' => 8,
                'min_length' => 8,
                'max_length' => 8,
                'mobile_prefixes' => json_encode(['60', '61', '62', '63', '64', '65', '66', '67', '68', '69', '70', '71', '72', '73', '74', '75', '76', '77', '78', '79']),
                'fixed_prefixes' => json_encode(['20', '21', '22', '23', '24', '25', '26', '27', '28', '29']),
                'premium_prefixes' => json_encode(['60', '61', '62', '63', '64']),
                'operators' => json_encode([
                    'Orange' => ['60', '61', '62', '63', '64', '65', '66', '67', '68', '69'],
                    'Telecel' => ['70', '71', '72', '73', '74', '75', '76', '77', '78', '79']
                ]),
                'validation_regex' => '^(6|7)[0-9]{7}$',
                'notes' => 'Mobile: 8 digits starting with 6 or 7.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_iso' => 'CM',
                'country_name' => 'Cameroun',
                'country_code' => '+237',
                'national_length' => 9,
                'min_length' => 9,
                'max_length' => 9,
                'mobile_prefixes' => json_encode(['65', '66', '67', '68', '69', '70', '71', '72', '73', '74', '75', '76', '77', '78', '79']),
                'fixed_prefixes' => json_encode(['22', '23', '24', '25', '26', '27', '28', '29', '33', '34', '35', '36', '37', '38', '39']),
                'premium_prefixes' => json_encode(['65', '66', '67', '68', '69']),
                'operators' => json_encode([
                    'Orange' => ['65', '66', '67', '68', '69'],
                    'MTN' => ['70', '71', '72', '73', '74', '75', '76', '77', '78', '79']
                ]),
                'validation_regex' => '^(6|7)[0-9]{8}$',
                'notes' => 'Mobile: 9 digits starting with 6 or 7.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_iso' => 'TG',
                'country_name' => 'Togo',
                'country_code' => '+228',
                'national_length' => 8,
                'min_length' => 8,
                'max_length' => 8,
                'mobile_prefixes' => json_encode(['90', '91', '92', '93', '94', '95', '96', '97', '98', '99']),
                'fixed_prefixes' => json_encode(['22', '23', '24', '25', '26', '27', '28', '29']),
                'premium_prefixes' => json_encode(['90', '91', '92', '93']),
                'operators' => json_encode([
                    'Moov' => ['90', '91', '92', '93', '94', '95', '96', '97', '98', '99'],
                    'Togocel' => ['90', '91', '92', '93', '94', '95', '96', '97', '98', '99']
                ]),
                'validation_regex' => '^9[0-9]{7}$',
                'notes' => 'Mobile: 8 digits starting with 9.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_iso' => 'BJ',
                'country_name' => 'Bénin',
                'country_code' => '+229',
                'national_length' => 8,
                'min_length' => 8,
                'max_length' => 8,
                'mobile_prefixes' => json_encode(['60', '61', '62', '63', '64', '65', '66', '67', '68', '69', '90', '91', '92', '93', '94', '95', '96', '97', '98', '99']),
                'fixed_prefixes' => json_encode(['20', '21', '22', '23', '24', '25', '26', '27', '28', '29']),
                'premium_prefixes' => json_encode(['60', '61', '62', '63', '64', '90', '91', '92', '93']),
                'operators' => json_encode([
                    'Moov' => ['60', '61', '62', '63', '64', '65', '66', '67', '68', '69'],
                    'MTN' => ['90', '91', '92', '93', '94', '95', '96', '97', '98', '99']
                ]),
                'validation_regex' => '^(6|9)[0-9]{7}$',
                'notes' => 'Mobile: 8 digits starting with 6 or 9.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('phone_validation_presets')->insert($presets);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phone_validation_presets');
    }
};
