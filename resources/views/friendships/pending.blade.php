<x-app-layout>
    <x-friendships.navbar-friends />

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

        <!-- Affichage des demandes en attente -->
        @if(!$pendingRequests->isEmpty())
            @foreach($pendingRequests as $request)
                @if($request->user_id != Auth::id())
                    <div class="flex items-center justify-between py-2 border-b border-gray-300 w-full px-4">
                        <span class="text-white">{{ $request->user->name }}</span>
                        <div class="flex space-x-2">
                            <form action="{{ route('friends.accept', $request->id) }}" method="POST">
                                @csrf
                                <button type="submit">
                                    <img src="{{ asset('source/assets/images/accept_icon.png') }}" alt="Accepter" class="h-10 w-10">
                                </button>
                            </form>
                            <form action="{{ route('friends.decline', $request->id) }}" method="POST">
                                @csrf
                                <button type="submit">
                                    <img src="{{ asset('source/assets/images/decline_icon.png') }}" alt="Refuser" class="h-10 w-10">
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            @endforeach
        @endif
    </div>
</x-app-layout>
