<nav x-data="{ open: false }" class="navBar w-full h-full bg-gray-800 text-white flex flex-col items-center justify-between py-6">
    <!-- Liens en haut -->
    <div class="flex flex-col items-center space-y-4">
        <!-- Lien vers Dashboard -->
        <a href="{{ route('dashboard') }}" class="flex items-center justify-center">
            <img src="{{ asset('source/assets/images/message_icon.png') }}" alt="Dashboard" class="h-8 w-8">
        </a>

        <!-- Lien vers Friendship -->
        <a href="{{ route('friends.index') }}" class="flex items-center justify-center">
            <img src="{{ asset('source/assets/images/friends_icon.png') }}" alt="Friends" class="h-8 w-8">
        </a>
    </div>

    <!-- Liens en bas -->
    <div class="flex flex-col items-center space-y-4 mt-auto">
        <!-- Lien vers Profil (Paramètres du compte) -->
        <a href="{{ route('profile.edit') }}" class="flex items-center justify-center">
            <img src="{{ asset('source/assets/images/account_icon.png') }}" alt="Profile" class="h-8 w-8">
        </a>

        <!-- Lien pour Se Déconnecter -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" onclick="event.preventDefault(); this.closest('form').submit();" class="flex items-center justify-center">
                <img src="{{ asset('source/assets/images/logout_icon.png') }}" alt="Logout" class="h-8 w-8">
            </button>
        </form>
    </div>
</nav>
