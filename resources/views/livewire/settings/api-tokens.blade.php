<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('API Tokens')" :subheading="__('Create and manage your API tokens')">
        <form wire:submit="createToken" class="my-6 w-full space-y-6">
            <flux:input wire:model="tokenName" :label="__('Token Name')" type="text" required placeholder="e.g. Mobile App" />

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('Create Token') }}</flux:button>

                <x-action-message class="me-3" on="token-created">
                    {{ __('Created.') }}
                </x-action-message>
            </div>
        </form>

        @if ($newToken)
            <div class="my-6">
                <flux:callout variant="warning">
                    <flux:callout.heading>{{ __('New Token Created') }}</flux:callout.heading>
                    <flux:callout.text>{{ __('Copy this token now. You won\'t be able to see it again.') }}</flux:callout.text>
                </flux:callout>

                <div class="mt-3">
                    <flux:input readonly value="{{ $newToken }}" copyable />
                </div>
            </div>
        @endif

        @if (auth()->user()->tokens->isNotEmpty())
            <flux:separator class="my-6" />

            <div class="space-y-4">
                <flux:heading size="sm">{{ __('Existing Tokens') }}</flux:heading>

                @foreach (auth()->user()->tokens as $token)
                    <div wire:key="token-{{ $token->id }}" class="flex items-center justify-between rounded-lg border p-3 dark:border-zinc-700">
                        <div>
                            <flux:text class="font-medium">{{ $token->name }}</flux:text>
                            <flux:text class="text-xs">{{ __('Created') }} {{ $token->created_at->diffForHumans() }}</flux:text>
                        </div>

                        <flux:button variant="danger" size="sm" wire:click="deleteToken({{ $token->id }})" wire:confirm="{{ __('Are you sure you want to delete this token?') }}">
                            {{ __('Delete') }}
                        </flux:button>
                    </div>
                @endforeach
            </div>
        @endif
    </x-settings.layout>
</section>
