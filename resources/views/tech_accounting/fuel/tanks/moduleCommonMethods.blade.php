<script>
    function getDatesDaysDiff(date1, date2) {
        date1 = new Date(date1);
        date2 = new Date(date2);
        return Math.floor((date2 - date1) / (1000*60*60*24))
    }

    function getEventDate() {
        if (typeof fuelFlowFormData['event_date'] === 'undefined') {
            return new Date();
        }
        return new Date(fuelFlowFormData['event_date'])
    }

    function getThreeDaysEarlierDate() {
        let date = new Date();
        date.setDate( date.getDate() - 3 )
        return date;
    }

    function isFuelFlowDataFieldUpdateAvailable(dataField) {
        // Если новая запись
        if (!Object.keys(fuelFlowFormData).includes('id')) {
            return false;
        }

        if (
            !Boolean("{{App::environment('local')}}")
            && Boolean(fuelFlowFormData['author_id'].author_id === authUserId)
            ) {

            return true;
        }
        
        const dateDiff = getDatesDaysDiff(fuelFlowFormData['created_at'], Date());

        if (
            dataField==='fuel_tank_id' 
            || dataField==='contractor_id' 
            || dataField==='volume'
            || dataField==='attachment'
            || dataField==='fuelConsumerType'
            || dataField==='our_technic_id'
            || dataField==='third_party_consumer'
            
        ) {
            if ( dateDiff < 1 ) {
                return false;
            }
        }

        if (dataField==='event_date') {
            if ( dateDiff < 3 ) {
                return false;
            }
        }

        return true;
    }
    
</script>