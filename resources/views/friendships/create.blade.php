<x-app-layout>
    <x-friendships.navbar-friends />
    <div class="mt-4 pt-10 w-full max-w-md mx-auto">
        <form action="{{ route('friends.store') }}" method="POST" class="space-y-6 dark:bg-gray-800 p-6 rounded-lg shadow-md">
            @csrf

            <div class="space-y-2">
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                    placeholder="Entrer le nom d'utilisateur">

                @error('name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <button id="add-friend-btn" type="submit" class="w-full text-white py-2 secondary-background-app transition-colors duration-200" onclick="addFriendWithLoad()">
                    Ajouter un ami
                </button>
                <span id="add-friend-loader" style="display:none;">
                    <div class="spinner"></div>
                </span>

            </div>

            <!-- Affichage du message de succès ou d'erreur sous le champ de saisie -->
            <div>
                @if(session('success'))
                <p class="text-green-500 mt-1">
                    {{ session('success') }}
                </p>
                @endif

                @if(session('error'))
                <p class="text-red-500 mt-1">
                    {{ session('error') }}
                </p>
                @endif
            </div>

        </form>
    </div>
</x-app-layout>

<script>
    /**
     * Function to display the loader when the user clicks on the button to add a friend
     */
    function addFriendWithLoad() {
        document.getElementById('add-friend-btn').style.display = 'none';
        document.getElementById('add-friend-loader').style.display = 'block';
    }
</script>
