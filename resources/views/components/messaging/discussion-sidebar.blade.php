<div class="w-1/5 secondary-background-app text-white border-r border-black overflow-y-auto h-full scrollbar-hide">
    <ul>
        <li class="flex items-center p-2 border-b border-black overflow-hidden mr-2">
            <div class="flex justify-between text-white w-full">
                <div>Discussions</div>
                <div>+</div>
            </div>
        </li>
        @foreach ($discussions as $discussion)
            <li class="flex items-center p-2 border-b border-black overflow-hidden mr-2 cursor-pointer"
                onclick="loadChat({{ $discussion->id }}, '{{ $discussion->name }}', {{true}})">
                <img src="{{ asset('source/assets/images/profile.png') }}" alt="Avatar" class="w-10 h-10 rounded-full mr-3">
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
