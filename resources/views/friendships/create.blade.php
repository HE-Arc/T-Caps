<x-app-layout>
    <x-friendships.navbar-friends />
    <div class="mt-4 pt-10 w-full max-w-md mx-auto">
        <form action="{{ route('friends.store') }}" method="POST" class="space-y-6 dark:bg-gray-800 p-6 rounded-lg shadow-md">
            @csrf

            <div class="space-y-2">
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                       placeholder="Enter friend's username">

                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <button type="submit" class="w-full text-white py-2 secondary-background-app transition-colors duration-200">
                    Add Friend
                </button>
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
