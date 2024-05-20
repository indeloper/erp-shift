<script>
    const authUserId = {!! Auth::id() !!};
    const userPermissions = {!! $userPermissions ?? '[]' !!};
    const additionalResources = {!! $additionalResources ?? '[]' !!};
</script>
