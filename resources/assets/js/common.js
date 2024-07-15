// let route = '{{ route('building::tech_acc::our_technic_tickets.report.store', [ID_TO_SUBSTITUTE, ID_TO_SUBSTITUTE, ID_TO_SUBSTITUTE]) }}';
// reportAdd.makeUrl(route, [1, 2, 3]);

function makeUrl(url, array_ids) {
    for (var i = 0; i < array_ids.length; i++) {
        url = url.replace('ID_TO_SUBSTITUTE', array_ids[i]);
    }

    return url;
}
