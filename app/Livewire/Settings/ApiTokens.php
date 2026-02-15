<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ApiTokens extends Component
{
    public string $tokenName = '';

    public string $newToken = '';

    /**
     * Create a new API token.
     */
    public function createToken(): void
    {
        $this->validate([
            'tokenName' => ['required', 'string', 'max:255'],
        ]);

        $token = Auth::user()->createToken($this->tokenName);

        $this->newToken = $token->plainTextToken;
        $this->tokenName = '';

        $this->dispatch('token-created');
    }

    /**
     * Delete an API token.
     */
    public function deleteToken(int $tokenId): void
    {
        Auth::user()->tokens()->where('id', $tokenId)->delete();

        $this->dispatch('token-deleted');
    }
}
