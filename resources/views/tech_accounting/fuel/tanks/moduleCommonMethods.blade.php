<script>
    function getDatesDaysDiff(date1, date2) {
        date1 = new Date(date1);
        date2 = new Date(date2);
        return Math.floor((date2 - date1) / (1000*60*60*24))
    }

    function getEventDate() {
        if (typeof fuelFlowFormData === 'undefined' || typeof fuelFlowFormData['event_date'] === 'undefined') {
            return new Date();
        }
        return new Date(fuelFlowFormData['event_date'])
    }

    function getDaysEarlierDate(qty) {
        let date = new Date();
        date.setDate( date.getDate() - qty )
        return date;
    }

    function isFuelFlowDataFieldUpdateAvailable(dataField) {
        // Если новая запись

        if (typeof fuelFlowFormData === 'undefined' || !Object.keys(fuelFlowFormData).includes('id')) {
            return false;
        }

        if (
            !Boolean("{{App::environment('local')}}")
            && Boolean(fuelFlowFormData['author_id'].author_id != authUserId)
            ) {
            return true;
        }

        if (dataField==='comment' ) {
            return false;
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
            || dataField==='event_date'
            
        ) {
            if ( dateDiff < 35 ) {
                return false;
            }
        }

        return true;
    }

    function getAvailableFuelTanksToCreateFlow(isAdjustmentPopup = null) {
        if(isAdjustmentPopup && userPermissions.adjust_fuel_tank_remains) {
            return additionalResources.fuelTanks
        }

        if(!userPermissions.create_fuel_tank_flows_for_any_tank) {
            return additionalResources.fuelTanks.filter(el=>el.responsible_id === authUserId).filter(el=>el.awaiting_confirmation != 1) 
        }
        return additionalResources.fuelTanks.filter(el=>el.awaiting_confirmation != 1) 
    }
    
</script>