<input type="hidden" id="title" value="@yield('title')">
<input type="hidden" id="url" value="@yield('url')">
<input type="hidden" id="user_url" value="{{ route('profile::show') }}">
<input type="hidden" id="logout_url" value="{{ route('logout') }}">
<input type="hidden" id="messages_index_url" value="{{ route('messages::index') }}">
<input type="hidden" id="notifications_index_url" value="{{ route('notifications::index') }}">
<input type="hidden" id="messages_count" value="{{ $messages }}">
<input type="hidden" id="notifications_count" value="{{ $notifications }}">

<div id="header-component"></div>