<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Carbon;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    // 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
    public function test_check_user_information()
    {
        $user1 = User::factory()->create()->first();
        $user2 = User::factory()->create()->first();

        $admin = Admin::factory()->create()->first();

        $response = $this->actingAs($admin, 'admin')->get("/admin/staff/list");

        $response->assertSeeInOrder([
            "{$user1->name}",
            "{$user1->email}",
            "詳細",
        ]);

        $response->assertSeeInOrder([
            "{$user2->name}",
            "{$user2->email}",
            "詳細",
        ]);
    }

    // ユーザーの勤怠情報が正しく表示される
    public function test_user_attendance_list()
    {
        $user = User::factory()->create()->first();

        $year = Carbon::now()->format('Y');
        $current_month = str_pad(Carbon::now()->format('m'), 2, '0', STR_PAD_LEFT);

        foreach (range(1, 5) as $i) {
            Attendance::create([
                'user_id' => $user->id,
                'clock_in' => Carbon::parse("{$year}-{$current_month}-0{$i}" . (8 + $i) . ":00:00"),
                'clock_out' => Carbon::parse("{$year}-{$current_month}-0{$i}" . (17 + $i) . ":00:00"),
            ]);
        }

        $admin = Admin::factory()->create()->first();

        $response = $this->actingAs($admin, 'admin')->get("/admin/attendance/month/list/{$user->id}");

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            "{$current_month}/01",'09:00','18:00',
            "{$current_month}/02",'10:00','19:00',
            "{$current_month}/03",'11:00','20:00',
            "{$current_month}/04",'12:00','21:00',
            "{$current_month}/05",'13:00','22:00',
        ]);
    }

    // 「前月」を押下した時に表示月の前月の情報が表示される
    public function test_prev_month()
    {
        $user = User::factory()->create()->first();
        $year = Carbon::now()->format('Y');
        $prev_month = str_pad(Carbon::now()->subMonthNoOverflow()->format('m'), 2, '0', STR_PAD_LEFT);

        foreach (range(1, 5) as $i) {
            Attendance::create([
                'user_id' => $user->id,
                'clock_in' => Carbon::parse("{$year}-{$prev_month}-0{$i}" . (8 + $i) . ":00:00"),
                'clock_out' => Carbon::parse("{$year}-{$prev_month}-0{$i}" . (17 + $i) . ":00:00"),
            ]);
        }

        $admin = Admin::factory()->create()->first();

        $response = $this->actingAs($admin, 'admin')->get("/admin/attendance/month/list/{$user->id}?month={$year}-{$prev_month}");

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            "{$prev_month}/01",'09:00','18:00',
            "{$prev_month}/02",'10:00','19:00',
            "{$prev_month}/03",'11:00','20:00',
            "{$prev_month}/04",'12:00','21:00',
            "{$prev_month}/05",'13:00','22:00',
        ]);
        $response->assertSee("{$year}/{$prev_month}");
    }

    // 「翌月」を押下した時に表示月の前月の情報が表示される
    public function test_next_month()
    {
        $user = User::factory()->create()->first();
        $year = Carbon::now()->format('Y');
        $next_month = str_pad(Carbon::now()->addMonthNoOverflow()->format('m'), 2, '0', STR_PAD_LEFT);

        foreach (range(1, 5) as $i) {
            Attendance::create([
                'user_id' => $user->id,
                'clock_in' => Carbon::parse("{$year}-{$next_month}-0{$i}" . (8 + $i) . ":00:00"),
                'clock_out' => Carbon::parse("{$year}-{$next_month}-0{$i}" . (17 + $i) . ":00:00"),
            ]);
        }

        $admin = Admin::factory()->create()->first();

        $response = $this->actingAs($admin, 'admin')->get("/admin/attendance/month/list/{$user->id}?month={$year}-{$next_month}");

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            "{$next_month}/01",'09:00','18:00',
            "{$next_month}/02",'10:00','19:00',
            "{$next_month}/03",'11:00','20:00',
            "{$next_month}/04",'12:00','21:00',
            "{$next_month}/05",'13:00','22:00',
        ]);
        $response->assertSee("{$year}/{$next_month}");
    }

    // 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function test_move_detail_attendnace()
    {
        $user = User::factory()->create()->first();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clock_in' => Carbon::parse('2025-05-25 09:00:00'),
            'clock_out' => Carbon::parse('2025-05-25 17:00:00'),
        ]);

        $admin = Admin::factory()->create()->first();
        $response = $this->actingAs($admin, 'admin')->get("/admin/attendance/month/list/{$user->id}?month=2025-05");
        $response->assertStatus(200);

        $response->assertSee("詳細");

        $detailResponse = $this->actingAs($admin, 'admin')->get("/admin/attendance/{$attendance->id}");
        $detailResponse->assertStatus(200);

        $detailResponse->assertSee('09:00');
        $detailResponse->assertSee('17:00');
    }
}
