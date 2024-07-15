@php $experience = $user->experienceAge @endphp
@if($user->group !== null){{ $user->group->name }},@endif @if($user->department !== null){{ $user->department->name }},@endif {{$user->full_name}} {{ now()->format('d.m.Y') }}. празднует  <strong>{{ trans_choice('{1} :count год|[2,4] :count года|[5,*] :count лет', $experience) }}</strong> работы в компании!
