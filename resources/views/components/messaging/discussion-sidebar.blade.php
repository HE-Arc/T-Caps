<div class="w-1/5 secondary-background-app text-white border-r border-black overflow-y-auto h-full scrollbar-hide">
    <ul>
        <li class="flex items-center p-2 border-b border-black overflow-hidden mr-2">
            <div class="flex justify-between text-white w-full">
                <div>
                    Discussions
                </div>
                <div>
                    <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'create-chat-modal')"
                        class="flex items-center justify-center">
                        <img src="{{ asset('source/assets/images/add.png') }}" alt="Add button" class="h-6 w-6">
                    </button>

                    <x-modal name="create-chat-modal" focusable>
                        <form method="post" action="{{ route('chats.store') }}" class="p-6" x-data="chatForm()"
                            x-on:submit.prevent="validateForm">
                            @csrf

                            <div class="mb-4">
                                <x-input-label for="chat-name" value="Nom de la discussion" />
                                <x-text-input id="chat-name" name="chat_name" type="text" class="block w-full mt-1"
                                    required x-model="chatName" />
                                <p x-show="errors.chatName" class="text-red-500 text-sm mt-1">Le nom de la discussion
                                    est requis.</p>
                            </div>

                            <div class="mb-4">
                                <p class="font-medium text-white">Sélectionnez le/les amis à ajouter à la discussion</p>
                                <div class="mt-2 overflow-y-auto max-h-48 scrollbar-hide rounded p-2">
                                    @foreach ($friends as $friend)
                                        <div
                                            class="flex items-center justify-between mb-2 p-2 rounded hover:bg-gray-700 transition-colors">
                                            <div class="flex items-center">
                                                @if ($friend->image)
                                                    <img src="{{ asset('storage/' . $friend->image) }}" alt="Avatar"
                                                        class="w-8 h-8 rounded-full mr-3">
                                                @else
                                                    <img src="{{ asset('source/assets/avatar/avatar.png') }}"
                                                        alt="Avatar" class="w-8 h-8 rounded-full mr-3">
                                                @endif
                                                <span class="text-white font-medium">{{ $friend->name }}</span>
                                            </div>
                                            <input type="checkbox" name="friends[]" value="{{ $friend->id }}"
                                                class="h-5 w-5 bg-gray-400 rounded-full focus:ring-0 border-none checked:bg-gray-500"
                                                x-model="selectedFriends">
                                        </div>
                                    @endforeach
                                    <p x-show="errors.friends" class="text-red-500 text-sm mt-1">Sélectionnez au moins
                                        un ami.</p>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <x-secondary-button x-on:click="$dispatch('close')">
                                    Annuler
                                </x-secondary-button>
                                <x-primary-button id="create-chat" class="ml-3">
                                    Créer la discussion
                                </x-primary-button>
                                <span id="create-chat-loader" style="display:none;">
                                    <div class="spinner"></div>
                                </span>
                            </div>
                        </form>
                    </x-modal>
                </div>
            </div>
        </li>
        @foreach ($discussions as $discussion)
            <li class="flex items-center p-2 border-b border-black overflow-hidden mr-2 cursor-pointer"
                onclick="loadChat({{ $discussion->id }}, '{{ $discussion->name }}', '{{ $discussion->discussionPicture }}', {{ $discussion->members }}, {{ true }})">
                <img src="{{ $discussion->discussionPicture }}" alt="Avatar"
                    class="w-10 h-10 rounded-full mr-3 flex-shrink-0">

                <div class="flex-1">
                    <div class="font-bold">{{ $discussion->name }}</div>
                    <div class="text-sm text-gray-400 whitespace-nowrap">
                        @if ($discussion->messages->first())
                            {{ $discussion->messages->first()->message }}
                        @endif
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
</div>


<script>
    /**
     * Function to validates form for chat creation
     */
    function chatForm() {
        return {
            chatName: '',
            selectedFriends: [],
            errors: {
                friends: false,
            },

            // Validates the form before submission
            validateForm() {
                // Check if any friends are selected
                this.errors.friends = this.selectedFriends.length === 0;

                // Get the button and loader elements
                let createChatBtn = document.getElementById('create-chat');
                let createChatLoader = document.getElementById('create-chat-loader');

                // Show the loader and hide the button while validating
                createChatBtn.style.display = 'none';
                createChatLoader.style.display = 'block';

                // Submit the form if no errors, otherwise keep the loader visible
                if (!this.errors.friends) {
                    this.$el.submit();
                }

                // Hide the loader and show the button again
                createChatBtn.style.display = 'block';
                createChatLoader.style.display = 'none';
            }
        }
    }
</script>
