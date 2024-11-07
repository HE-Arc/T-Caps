<x-app-layout>
    <x-navbar-friends />

    <!-- Décalage du contenu pour éviter la superposition avec la barre rouge -->
    <div class="pt-10 w-full">
        @if(session('error'))
            <div class="bg-red-500 text-white p-4 rounded mb-4 mx-4">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="bg-green-500 text-white p-4 rounded mb-4 mx-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Liste d'amis en format ligne pleine largeur avec séparateurs -->
        @if($friends->isEmpty())
            <p class="text-white px-4">You have no friends yet.</p>
        @else
            @foreach($friends as $friend)
                <div class="flex items-center justify-between py-4 border-b border-gray-300 w-full px-4">
                    <span class="text-white">
                        {{ $friend->user_id == Auth::id() ? $friend->friend->name : $friend->user->name }}
                    </span>
                    <div class="flex space-x-2">
                        <!-- Boutons d'action en icônes -->
                         
                            <button>
                                <img src="{{ asset('source/assets/images/message_friend_icon.png') }}" alt="Message" class="h-10 w-10">
                            </button>
                            
                            <form action="{{ route('friends.destroy', ['friend' => $friend->user_id == Auth::id() ? $friend->friend->id : $friend->user->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this friend?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit">
                                    <img src="{{ asset('source/assets/images/delete_friend_icon.png') }}" alt="Supprimer" class="h-10 w-10">
                                </button>
                            </form>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</x-app-layout>
