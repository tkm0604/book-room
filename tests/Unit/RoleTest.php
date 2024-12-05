<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_it_can_create_a_role()
    {
        $role = Role::factory()->create();

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
        ]);
    }

    /** @test */
    public function test_it_has_users_relation()
    {
        $role = Role::factory()->create();
        $user = User::factory()->create();

        $role->users()->attach($user);

        $this->assertTrue($role->users->contains($user));
    }
}
