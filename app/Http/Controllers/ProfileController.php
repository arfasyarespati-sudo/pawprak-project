<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * Update the authenticated user's display name and avatar.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated): void {
            /** @var User $user */
            $user = Auth::user();

            $user->update([
                'name' => $validated['name'],
                'avatar' => $validated['avatar'],
            ]);

            if ($user->player) {
                $user->player->update([
                    'nickname' => $validated['name'],
                    'avatar' => $validated['avatar'],
                ]);
            }
        });

        return back()->with('success', 'Profil berhasil diperbarui!');
    }
}
