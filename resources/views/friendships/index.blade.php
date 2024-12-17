<x-app-layout>
    <x-friendships.navbar-friends />

    <div class="mt-4 pt-10 w-full">
        @if (session('error'))
            <div class="bg-red-500 text-white p-4 rounded mb-4 mx-4">
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-500 text-white p-4 rounded mb-4 mx-4">
                {{ session('success') }}
            </div>
        @endif

        @if ($friends->isEmpty())
            <p class="text-white px-4">Vous n'avez pas encore ajouté d'ami</p>
        @else
            @foreach ($friends as $friend)
                <div class="flex items-center justify-between py-4 border-b border-gray-300 w-full px-4">
                    <div class="flex items-center">
                        @php
                            $image = $friend->user_id == Auth::id() ? $friend->friend->image : $friend->user->image;
                        @endphp
                        @if ($image)
                            <img src="{{ asset('storage/' . $image) }}" alt="Avatar" class="w-8 h-8 rounded-full mr-3">
                        @else
                            <img src="{{ asset('source/assets/avatar/avatar.png') }}" alt="Avatar"
                                class="w-8 h-8 rounded-full mr-3">
                        @endif

                        <span class="text-white">
                            {{ $friend->user_id == Auth::id() ? $friend->friend->name : $friend->user->name }}
                        </span>
                    </div>

                    <div class="flex space-x-2">
                        <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'delete-modal-{{ $friend->id }}')"
                            class="flex items-center justify-center">
                            <img src="{{ asset('source/assets/images/delete_friend_icon.png') }}" alt="Supprimer"
                                class="h-10 w-10">
                        </button>
                        <x-modal name="delete-modal-{{ $friend->id }}" focusable>
                            <form
                                action="{{ route('friends.destroy', ['friend' => $friend->user_id == Auth::id() ? $friend->friend->id : $friend->user->id]) }}"
                                method="POST" class="p-6">
                                @csrf
                                @method('DELETE')
                                <div class="mb-4">
                                    <p class="text-white text-center">Etes-vous sûr de vouloir supprimer cet ami ?
                                </div>

                                <div class="mt-6 flex justify-center">
                                    <x-secondary-button x-on:click="$dispatch('close')">
                                        Non
                                    </x-secondary-button>
                                    <x-primary-button id="delete-friend" class="ml-3">
                                        Oui
                                    </x-primary-button>
                                </div>
                            </form>
                        </x-modal>

                        <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'block-modal-{{ $friend->id }}')"
                            class="flex items-center justify-center">
                            <img src="{{ asset('source/assets/images/block_icon.png') }}" alt="Block"
                                    class="h-10 w-10">
                        </button>
                        <x-modal name="block-modal-{{ $friend->id }}" focusable>
                            <form
                                action="{{ route('friends.block', ['friend' => $friend->user_id == Auth::id() ? $friend->friend->id : $friend->user->id]) }}"
                                method="POST" class="p-6">
                                @csrf
                                <div class="mb-4">
                                    <p class="text-white text-center">Etes-vous sûr de vouloir bloquer cet ami ?
                                </div>

                                <div class="mt-6 flex justify-center">
                                    <x-secondary-button x-on:click="$dispatch('close')">
                                        Non
                                    </x-secondary-button>
                                    <x-primary-button id="block-friend" class="ml-3">
                                        Oui
                                    </x-primary-button>
                                </div>
                            </form>
                        </x-modal>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</x-app-layout>
