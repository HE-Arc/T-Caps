<x-app-layout>
    <h1 class="text-2xl font-bold mb-4">Friends List</h1>

    @if($friends->isEmpty())
        <p class="text-gray-600">You have no friends yet.</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                <tr class="bg-gray-100 border-b">
                    <th scope="col" class="text-center px-4 py-2 border-r text-sm font-medium text-gray-700">Name</th>
                    <th scope="col" class="text-center px-4 py-2 text-sm font-medium text-gray-700">Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($friends as $friend)
                    <tr class="border-b">
                        <td class="text-center px-4 py-2 border-r">
                            {{-- Affichage du nom de l'ami --}}
                            {{ $friend->user_id == Auth::id() ? $friend->friend->name : $friend->user->name }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-app-layout>
