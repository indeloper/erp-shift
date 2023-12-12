@include('tech_accounting.fuel.tanks.moduleCommonVariables')

<script>
    // Общие
    let editingRowId = 0;
    // Используется при подтверждении закрытия формы без сохранения
    let skipStoppingEditingRow = 0;
    let userPermissions = JSON.parse("{{$userPermissions}}".replace(/&quot;/g,'"'));
    const authUserId = +"{{Auth::user()->id}}"
    // Конец Общие
    let choosedFormTab = '';

    

</script>
