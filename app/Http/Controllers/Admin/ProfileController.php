<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Admin\Concerns\UploadsImages;
use App\Http\Controllers\Controller;
use App\Services\Admin\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * الملف الشخصي للمستخدم الحالي داخل لوحة التحكم:
 * البيانات الشخصية، الصورة، كلمة المرور.
 */
class ProfileController extends Controller
{
    use InteractsWithAdminPanel;
    use UploadsImages;

    public function edit()
    {
        return view('admin.profile.edit', [
            'user' => $this->admin(),
            'title' => 'الملف الشخصي',
        ]);
    }

    public function update(Request $request)
    {
        $user = $this->admin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'job_title' => ['nullable', 'string', 'max:150'],
            'preferred_locale' => ['nullable', 'in:ar,en'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        if ($request->hasFile('avatar')) {
            $path = $this->uploadImage($request->file('avatar'), 'avatars');

            if ($path) {
                $this->deleteUpload($user->avatar_path);
                $data['avatar_path'] = $path;
            }
        }

        unset($data['avatar']);

        $user->update($data);

        AuditLogger::log('profile.updated', $user);

        return back()->with('success', 'تم تحديث بياناتك بنجاح.');
    }

    public function password(Request $request)
    {
        $user = $this->admin();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check((string) $request->input('current_password'), (string) $user->password)) {
            return back()->withErrors(['current_password' => 'كلمة المرور الحالية غير صحيحة.']);
        }

        $user->update(['password' => Hash::make((string) $request->input('password'))]);

        AuditLogger::log('profile.password', $user);

        return back()->with('success', 'تم تغيير كلمة المرور.');
    }
}
