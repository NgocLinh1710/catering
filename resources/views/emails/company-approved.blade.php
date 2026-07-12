<h2>Tài khoản doanh nghiệp đã được phê duyệt</h2>

<p>Xin chào {{ $user->name }},</p>

<p>Tài khoản của bạn đã được kích hoạt.</p>

<p>Email đăng nhập: <b>{{ $user->email }}</b></p>
<p>Mật khẩu tạm thời: <b>{{ $plainPassword }}</b></p>

<p>Vui lòng đổi mật khẩu trước {{ $user->password_change_deadline->format('d/m/Y H:i') }}.</p>

<p>Trân trọng,<br>Hệ thống Catering</p>