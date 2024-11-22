<div class="w-1/5 secondary-background-app text-white border-r border-black overflow-y-auto h-full scrollbar-hide">
    <ul>
        <li class="flex items-center p-2 border-b border-black overflow-hidden mr-2">
            <div class="flex justify-between text-white w-full">
                <div>
                    Discussions
                </div>
                <div>
                    <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')" class="flex items-center justify-center">
                        <img src="{{ asset('source/assets/images/add.png') }}" alt="Add button" class="h-6 w-6">
                    </button>

                    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
                        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
                            @csrf
                            @method('delete')

                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('Are you sure you want to delete your account?') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                            </p>

                            <div class="mt-6">
                                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                                <x-text-input id="password" name="password" type="password" class="mt-1 block w-3/4"
                                    placeholder="{{ __('Password') }}" />

                                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                            </div>

                            <div class="mt-6 flex justify-end">
                                <x-secondary-button x-on:click="$dispatch('close')">
                                    {{ __('Cancel') }}
                                </x-secondary-button>

                                <x-danger-button class="ms-3">
                                    {{ __('Delete Account') }}
                                </x-danger-button>
                            </div>
                        </form>
                    </x-modal>
                </div>
            </div>
        </li>
        @foreach ($discussions as $discussion)
            <li class="flex items-center p-2 border-b border-black overflow-hidden mr-2 cursor-pointer"
                onclick="loadChat({{ $discussion->id }}, '{{ $discussion->name }}', {{ true }})">
                <img src="{{ asset('source/assets/images/profile.png') }}" alt="Avatar"
                    class="w-10 h-10 rounded-full mr-3 flex-shrink-0"> <!-- Ajout de flex-shrink-0 pour éviter que l'image rétrécisse -->
                <div class="flex-1">
                    <div class="font-bold">{{ $discussion->name }}</div>
                    <div class="text-sm text-gray-400 whitespace-nowrap">
                        {{ $discussion->messages->first()->message }}
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
</div>
