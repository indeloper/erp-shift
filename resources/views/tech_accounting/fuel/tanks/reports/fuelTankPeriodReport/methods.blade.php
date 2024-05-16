<script>
    function addFilterParamToCurrentLoadOptions(param, value) {
        if(currentLoadOptionsParams.filter.length) {
            currentLoadOptionsParams.filter.push("and")
        }
        const newParamArr = []
        newParamArr.push(param)
        newParamArr.push("=")
        newParamArr.push(value)
        currentLoadOptionsParams.filter.push(newParamArr)
    }

    function clearCurrentLoadOptionsFilterParam(param) {
        currentLoadOptionsParams.filter = currentLoadOptionsParams.filter.filter(el=>el[0] != param)
    }
</script>
