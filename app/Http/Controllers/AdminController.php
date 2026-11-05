<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->where('is_admin', false)
            ->withCount(['expenses', 'incomes'])
            ->orderBy('username')
            ->get();

        return view('admin.index', compact('users'));
    }

    public function createUser(): View
    {
        return view('admin.create-user');
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $usernameInput = $request->input('username');
        if (is_string($usernameInput)) {
            $request->merge(['username' => Str::lower(trim($usernameInput))]);
        }

        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'alpha_dash',
                Rule::notIn([Str::lower((string) config('admin.username'))]),
                'unique:users,username',
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.min' => 'Username minimal 3 karakter.',
            'username.max' => 'Username maksimal 30 karakter.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
            'username.not_in' => 'Username tersebut khusus untuk administrator.',
            'username.unique' => 'Username sudah digunakan.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        User::create([
            'name' => $validated['username'],
            'username' => $validated['username'],
            'password' => $validated['password'],
        ]);

        return redirect()
            ->route('admin.index')
            ->with('success', "Akun {$validated['username']} berhasil dibuat.");
    }

    public function resetFinances(Request $request, User $user): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $user = $this->findManageableUser($user);

        DB::transaction(function () use ($user): void {
            $user->expenses()->delete();
            $user->incomes()->delete();
        });

        $message = "Data keuangan akun {$user->username} berhasil direset.";

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'expensesCount' => 0,
                'incomesCount' => 0,
            ]);
        }

        return redirect()->route('admin.index')->with('success', $message);
    }

    public function destroyUser(Request $request, User $user): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $user = $this->findManageableUser($user);
        $username = $user->username;

        DB::transaction(fn () => $user->delete());

        $message = "Akun {$username} berhasil dihapus.";

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return redirect()->route('admin.index')->with('success', $message);
    }

    private function findManageableUser(User $user): User
    {
        return User::query()
            ->where('is_admin', false)
            ->findOrFail($user->id);
    }
}
