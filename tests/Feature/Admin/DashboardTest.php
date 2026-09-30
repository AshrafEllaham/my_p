<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class DashboardTest extends TestCase
{
    public function test_dashboard_home_renders_the_master_layout(): void
    {
        $this->withoutVite();

        $response = $this->get(route('admin.home'));

        $response
            ->assertOk()
            ->assertViewIs('admin.home.index')
            ->assertSee(__('admin.welcome'))
            ->assertSee(__('admin.panel_name'));
    }
}
