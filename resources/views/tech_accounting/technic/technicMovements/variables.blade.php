<script>
// Общие
    let editingRowId = 0;
    // Используется при подтверждении закрытия формы без сохранения
    let skipStoppingEditingRow = 0;
    const userPermissions = JSON.parse("{{$userPermissions}}".replace(/&quot;/g,'"'));
    const authUserId = +"{{Auth::user()->id}}"
// Конец Общие
let newComments = [];


</script>