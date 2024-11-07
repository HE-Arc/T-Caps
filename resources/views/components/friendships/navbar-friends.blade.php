
<div class="absolute top-0 left-0 w-full h-12 flex items-center justify-between px-4 navBar">
    <div class="flex items-center space-x-4">
        <a href="{{ route('friends.index') }}" class="text-white font-semibold py-1 px-3 rounded-full
        {{ Route::currentRouteName() === 'friends.index' ? 'btn-active' : 'btn-inactive' }}">
            Amis
        </a>
        <a href="{{ route('friends.pending') }}" class="text-white font-semibold py-1 px-3 rounded-full
        {{ Route::currentRouteName() === 'friends.pending' ? 'btn-active' : 'btn-inactive' }}">
            En attente
        </a>
    </div>
    <!-- Bouton "Ajouter" aligné à droite -->
    <div class="ml-auto">
        <a href="{{ route('friends.create') }}" class="text-white btn-inactive font-semibold py-1 px-3 rounded-full
            {{ Route::currentRouteName() === 'friends.create' ? 'btn-active' : 'btn-inactive' }}">
            Ajouter
        </a>
    </div>
</div>
