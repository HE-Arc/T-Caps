<nav x-data="{ open: false }" class="secondary-background-app border-r border-black w-full h-full text-white flex flex-col items-center justify-between py-6">
    <div class="flex flex-col items-center space-y-4">
        <a href="{{ route('dashboard') }}" class="flex items-center justify-center">
            <img src="{{ asset('source/assets/images/message_icon.png') }}" alt="Dashboard" class="h-8 w-8">
        </a>

        <a href="{{ route('friends.index') }}" class="flex items-center justify-center">
            <img src="{{ asset('source/assets/images/friends_icon.png') }}" alt="Friends" class="h-8 w-8">
        </a>
    </div>

    <div class="flex flex-col items-center space-y-4 mt-auto">
        <a href="{{ route('profile.edit') }}" class="flex items-center justify-center">
            <img src="{{ asset('source/assets/images/account_icon.png') }}" alt="Profile" class="h-8 w-8">
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" onclick="event.preventDefault(); this.closest('form').submit();" class="flex items-center justify-center">
                <img src="{{ asset('source/assets/images/logout_icon.png') }}" alt="Logout" class="h-8 w-8">
            </button>
        </form>
    </div>
</nav>
