<script>
// Общие
    let editingRowId = 0;
    // Используется при подтверждении закрытия формы без сохранения
    let skipStoppingEditingRow = 0;
    const userPermissions = JSON.parse("{{$userPermissions}}".replace(/&quot;/g,'"'));
// Конец Общие

    const datafieldsTechnicOwnerGroupGroup1 = [
        'company_id', 
        'inventory_number', 
        'exploitation_start', 
        'responsible_id', 
        'serial_number',
        'manufacture_year',
        'registration_number'
    ];

    const datafieldsTechnicOwnerGroupGroup2 = [
        'contractor_id'
    ];

</script>
