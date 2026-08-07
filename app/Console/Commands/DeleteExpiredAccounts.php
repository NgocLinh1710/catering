<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class DeleteExpiredAccounts extends Command
{
    protected $signature = 'app:delete-expired-accounts';

    protected $description = 'Xóa tài khoản chưa đổi mật khẩu sau 48 giờ';

    public function handle()
    {
        $count = User::where('must_change_password', true)
            ->where('password_change_deadline', '<', now())
            ->delete();

        $this->info("Đã xóa {$count} tài khoản.");

        return Command::SUCCESS;
    }
}