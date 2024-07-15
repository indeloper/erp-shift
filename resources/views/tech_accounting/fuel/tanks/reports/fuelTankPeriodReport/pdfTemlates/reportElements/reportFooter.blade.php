<htmlpagefooter name="page-footer">
    <footer>
        {{$carbonInstance::create(now())->format('d.m.Y H:i')}}, 
        {{$userModelInstance::find(Auth::user()->id)->user_full_name}},
        отчет за период {{$dateFrom}} - {{$dateTo}}
    </footer>       
</htmlpagefooter>