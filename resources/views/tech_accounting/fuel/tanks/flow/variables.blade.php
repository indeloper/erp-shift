<script>
    // Общие
    let editingRowId = 0;
    // Используется при подтверждении закрытия формы без сохранения
    let skipStoppingEditingRow = 0;
    let currentLoadOptions = [];
    const userPermissions = JSON.parse("{{$userPermissions}}".replace(/&quot;/g,'"'));
    const authUserId = +"{{Auth::user()->id}}"

    // Конец Общие
    const months =
        {
            1: "Январь",
            2: "Февраль",
            3: "Март",
            4: "Апрель",
            5: "Май",
            6: "Июнь",
            7: "Июль",
            8: "Август",
            9: "Сентябрь",
            10: "Октябрь",
            11: "Ноябрь",
            12: "Декабрь"
        }
</script>