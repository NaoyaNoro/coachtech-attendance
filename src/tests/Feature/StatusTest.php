<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Status;
use App\Models\Attendance;

class StatusTest extends TestCase
{
    use RefreshDatabase;

    // 勤務外の場合、勤怠ステータスが正しく表示される
    public function test_status_is_displayed_as_before_working()
    {
        $user = User::factory()->create()->first();
        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    // 出勤中の場合、勤怠ステータスが正しく表示される
    public function test_status_is_displayed_as_working()
    {
        $user = User::factory()->create()->first();
        Status::create([
            'user_id' => $user->id,
            'status' => 'working',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'clock_in' => now(),
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    // 休憩中の場合、勤怠ステータスが正しく表示される
    public function test_status_is_displayed_as_breaking()
    {
        $user = User::factory()->create()->first();
        Status::create([
            'user_id' => $user->id,
            'status' => 'breaking',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'clock_in' => now(),
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    // 退勤済の場合、勤怠ステータスが正しく表示される
    public function test_status_is_displayed_as_after_working()
    {
        $user = User::factory()->create()->first();
        Status::create([
            'user_id' => $user->id,
            'status' => 'after_working',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'clock_in' => now(),
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}
