<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'     => ['required', 'confirmed', Password::defaults()],
            'company_name' => ['required', 'string', 'max:255'],
            'country'      => ['required', 'in:TR,US,UK,EU,AE'],
            'tax_number'   => ['sometimes', 'string', 'max:50'],
        ]);

        $user = DB::transaction(function () use ($validated): User {
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            Company::create([
                'user_id'    => $user->id,
                'name'       => $validated['company_name'],
                'country'    => $validated['country'],
                'tax_number' => $validated['tax_number'] ?? '',
            ]);

            return $user;
        });

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user'    => $user->load('company'),
            'token'   => $token,
        ], 201);
    }
}
