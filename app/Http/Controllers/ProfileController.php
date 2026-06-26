<?php
namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Services\FileUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    public function update(UpdateProfileRequest $request, FileUploadService $fileUpload): RedirectResponse
    {
        $user = auth()->user();
        $data = $request->validated();

        if ($request->hasFile('avatar') && !$user->is_guest) {
            $data['avatar'] = $fileUpload->storeAvatar($request->file('avatar'), $user->id);
        }

        unset($data['avatar_input']); // safety
        $user->update(array_filter($data, fn($v) => $v !== null));
        return back()->with('success', 'Profile updated!');
    }
}
