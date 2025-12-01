<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

use function redirect;

use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Requests\Users\BulkDeleteUsersRequest;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $users = User::query()
            ->latest()
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'uuid' => $user->uuid,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at?->toDateTimeString(),
                'created_at' => $user->created_at->toDateTimeString(),
            ]);

        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Remove multiple users from storage.
     */
    public function bulkDestroy(BulkDeleteUsersRequest $request): RedirectResponse
    {
        $deletedCount = User::whereIn('uuid', $request->uuids)->delete();

        return redirect()->route('users.index')
            ->with('success', "{$deletedCount} user(s) deleted successfully.");
    }
}
