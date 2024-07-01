<b>{{ $notificationData->getData()['name'] }}</b>

<pre><code>{{ $notificationData->getData()['exceptionMessage'] }}</code></pre>

<blockquote>USER: {{ $notificationData->getData()['user']->id }} {{ $notificationData->getData()['user']->user_full_name }}</blockquote>

<blockquote>IP: {{ $notificationData->getData()['ip'] }}</blockquote>
