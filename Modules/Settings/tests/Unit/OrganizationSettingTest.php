<?php

namespace Modules\Settings\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Settings\app\Models\OrganizationSetting;

class OrganizationSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /** @test */
    public function it_can_create_organization_settings()
    {
        $settings = OrganizationSetting::create([
            'organization_name' => 'Acme Corporation',
            'address' => '123 Business St, City, Country',
            'phone' => '+1234567890',
            'email' => 'info@acme.com',
            'website' => 'https://acme.com',
            'timezone' => 'UTC',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i:s',
            'default_language' => 'en',
            'available_languages' => ['en', 'ar', 'fr'],
            'currency' => 'USD',
            'currency_symbol' => '$',
            'enable_notifications' => true,
            'enable_audit_logs' => true,
            'primary_color' => '#1E40AF',
            'secondary_color' => '#7C3AED',
        ]);

        $this->assertDatabaseHas('organization_settings', [
            'organization_name' => 'Acme Corporation',
            'currency' => 'USD',
        ]);

        $this->assertEquals('Acme Corporation', $settings->organization_name);
        $this->assertEquals('$', $settings->currency_symbol);
    }

    /** @test */
    public function it_follows_singleton_pattern()
    {
        $settings1 = OrganizationSetting::firstOrCreate([]);
        $settings2 = OrganizationSetting::firstOrCreate([]);

        $this->assertEquals($settings1->id, $settings2->id);
    }

    /** @test */
    public function it_casts_enable_notifications_to_boolean()
    {
        $settings = OrganizationSetting::create([
            'enable_notifications' => true,
            'enable_audit_logs' => false,
        ]);

        $this->assertIsBool($settings->enable_notifications);
        $this->assertIsBool($settings->enable_audit_logs);
        $this->assertTrue($settings->enable_notifications);
        $this->assertFalse($settings->enable_audit_logs);
    }

    /** @test */
    public function it_casts_available_languages_to_array()
    {
        $settings = OrganizationSetting::create([
            'available_languages' => ['en', 'ar', 'fr'],
        ]);

        $this->assertIsArray($settings->available_languages);
        $this->assertCount(3, $settings->available_languages);
        $this->assertContains('en', $settings->available_languages);
    }

    /** @test */
    public function it_checks_if_language_is_available()
    {
        $settings = OrganizationSetting::create([
            'available_languages' => ['en', 'ar', 'fr'],
        ]);

        $this->assertTrue($settings->isLanguageAvailable('en'));
        $this->assertTrue($settings->isLanguageAvailable('ar'));
        $this->assertTrue($settings->isLanguageAvailable('fr'));
        $this->assertFalse($settings->isLanguageAvailable('de'));
    }

    /** @test */
    public function it_can_update_settings()
    {
        $settings = OrganizationSetting::create([
            'organization_name' => 'Old Name',
            'currency' => 'USD',
        ]);

        $settings->update([
            'organization_name' => 'New Name',
            'currency' => 'EUR',
            'currency_symbol' => '€',
        ]);

        $this->assertEquals('New Name', $settings->organization_name);
        $this->assertEquals('EUR', $settings->currency);
        $this->assertEquals('€', $settings->currency_symbol);

        $this->assertDatabaseHas('organization_settings', [
            'id' => $settings->id,
            'organization_name' => 'New Name',
            'currency' => 'EUR',
        ]);
    }

    /** @test */
    public function it_has_nullable_fields()
    {
        $settings = OrganizationSetting::create([]);

        $this->assertNull($settings->organization_name);
        $this->assertNull($settings->address);
        $this->assertNull($settings->phone);
        $this->assertNull($settings->email);
        $this->assertNull($settings->website);
        $this->assertNull($settings->logo_path);
    }

    /** @test */
    public function it_stores_branding_colors()
    {
        $settings = OrganizationSetting::create([
            'primary_color' => '#FF5733',
            'secondary_color' => '#33FF57',
        ]);

        $this->assertEquals('#FF5733', $settings->primary_color);
        $this->assertEquals('#33FF57', $settings->secondary_color);
    }

    /** @test */
    public function it_stores_ceo_information()
    {
        $settings = OrganizationSetting::create([
            'ceo_name' => 'John Doe',
            'ceo_email' => 'ceo@acme.com',
            'ceo_phone' => '+1234567890',
        ]);

        $this->assertEquals('John Doe', $settings->ceo_name);
        $this->assertEquals('ceo@acme.com', $settings->ceo_email);
        $this->assertEquals('+1234567890', $settings->ceo_phone);
    }

    /** @test */
    public function it_stores_regional_settings()
    {
        $settings = OrganizationSetting::create([
            'timezone' => 'America/New_York',
            'date_format' => 'm/d/Y',
            'time_format' => 'h:i A',
        ]);

        $this->assertEquals('America/New_York', $settings->timezone);
        $this->assertEquals('m/d/Y', $settings->date_format);
        $this->assertEquals('h:i A', $settings->time_format);
    }

    /** @test */
    public function it_has_timestamps()
    {
        $settings = OrganizationSetting::create([
            'organization_name' => 'Test Corp',
        ]);

        $this->assertNotNull($settings->created_at);
        $this->assertNotNull($settings->updated_at);
    }

    /** @test */
    public function available_languages_defaults_to_empty_array_when_null()
    {
        $settings = OrganizationSetting::create([
            'organization_name' => 'Test Corp',
        ]);

        // When available_languages is null in DB, it should be cast to empty array
        $this->assertIsArray($settings->available_languages);
    }
}
