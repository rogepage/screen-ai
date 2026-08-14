<?php

namespace Tests\Unit;

use Tests\TestCase;

class BuildConfigurationTest extends TestCase
{
    public function test_application_version_is_unified_across_configs(): void
    {
        $screenAiVersion = config('screen_ai.version');
        $nativePhpVersion = config('nativephp.version');

        $this->assertEquals('1.0.0', $screenAiVersion);
        $this->assertEquals('1.0.0', $nativePhpVersion);
        $this->assertEquals($screenAiVersion, $nativePhpVersion);
    }

    public function test_nativephp_app_id_is_correctly_configured(): void
    {
        $appId = config('nativephp.app_id');
        $this->assertEquals('com.screenai.desktop', $appId);
    }

    public function test_nativephp_prebuild_includes_npm_build(): void
    {
        $prebuild = config('nativephp.prebuild', []);
        $this->assertContains('npm run build', $prebuild);
    }

    public function test_cleanup_env_keys_safeguards_secrets(): void
    {
        $cleanupKeys = config('nativephp.cleanup_env_keys', []);
        $this->assertContains('*_SECRET', $cleanupKeys);
        $this->assertContains('AWS_*', $cleanupKeys);
    }
}
