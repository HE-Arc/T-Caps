@php
    $members = [
        ['name' => 'Jean Dupont', 'avatar' => 'https://via.placeholder.com/40'],
        ['name' => 'Marie Curie', 'avatar' => 'https://via.placeholder.com/40'],
        ['name' => 'Albert Einstein', 'avatar' => 'https://via.placeholder.com/40'],
    ];
@endphp

<div class="flex background-app border-b border-black h-12 items-center justify-between p-4 gap-x-4">
    <div class="flex items-center gap-x-4">
        <img src="" alt="Discussion Picture" class="w-10 h-10 rounded-full headerImage">
        <span class="text-white font-semibold headerTitle"></span>
    </div>
    <div class="flex items-center gap-x-4">

        <button id="info-chat-btn"
            x-data="" x-on:click.prevent="$dispatch('open-modal', 'info-modal')">
            <img src="{{ asset('source/assets/images/info_icon.png') }}" alt="Profile" class="h-8 w-8">
        </button>
    </div>
</div>

<x-modal name="info-modal" focusable>
    <div class="p-6">
        <h2 class="text-lg font-bold mb-4 text-white">Membres du groupe</h2>
        <ul class="text-white space-y-2" id="members-list">
        </ul>
        <div class="mt-6 flex justify-between">
            <button id="leave-chat-btn"
                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                onclick="leaveChat()">
                Quitter le groupe
            </button>
            <x-secondary-button x-on:click="$dispatch('close')">
                Fermer
            </x-secondary-button>
        </div>
    </div>
</x-modal>
