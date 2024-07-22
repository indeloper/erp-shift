@extends('layouts.telegram')

@section('content')
    <div class="container">
        <h1>!! Данные !!</h1>

        <ul>
            <li>{{ auth()->user()->first_name }}</li>
            <li>{{ auth()->user()->last_name }}</li>
            <li>{{ auth()->user()->patronymic }}</li>
            <li>{{ auth()->user()->birthday }}</li>
            <li>{{ auth()->user()->email }}</li>
            <li>{{ auth()->user()->person_phone }}</li>
        </ul>

        <pre>
             {{ print_r(auth()->user()->toArray()) }}
         </pre>
    </div>
@endsection