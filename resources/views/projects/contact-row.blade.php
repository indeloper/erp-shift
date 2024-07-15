<tr style="cursor:default" class="key-{{ $key }}">
    <td data-label="ФИО" class="collapsed tr-pointer" aria-expanded="false">
        {{ isset($contact->last_name) ? $contact->last_name : $contact['last_name'] }}
        {{ isset($contact->first_name) ? $contact->first_name : $contact['first_name'] }}
        {{ isset($contact->patronymic) ? $contact->patronymic : $contact['patronymic'] }}
    </td>
    <td data-label="Должность" class="collapsed tr-pointer" aria-expanded="false">
        {{ isset($contact->position) ? $contact->position : $contact['position'] }}
    </td>
    <td data-label="Контактный номер">
        @if (! isset($contact->phones) && is_array($contact['phones']))
            {{ $contact['phones']['name'] . ': ' . $contact['phones']['phone_number'] . ' ' . $contact['phones']['dop_phone'] }}
        @elseif ($contact->phones->where('is_main', 1)->count() > 0)
            {{ $contact->phones->where('is_main', 1)->pluck('name')->first() . ': ' . $contact->phones->where('is_main', 1)->pluck('phone_number')->first() . ' ' . $contact->phones->where('is_main', 1)->pluck('dop_phone')->first() }}
        @endif
    </td>
    <td data-label="email">
        {{ isset($contact->email) ? $contact->email : $contact['email'] }}
    </td>
    <td class="text-right">
        <button type="button" class="btn-danger btn-link btn-sm mn-0 pd-0" onclick="removeContact({{ $key }})">
            <i class="fa fa-times"></i>
        </button>
    </td>
</tr>
