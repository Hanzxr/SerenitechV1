@extends('layouts.student')

@section('content')
<div class="container">
    <h2 class="mb-4">🔴 Live Video Call Session</h2>

    <div id="jitsi-container" style="height: 600px; width: 100%; border: 2px solid #ccc; border-radius: 8px;"></div>
</div>

<script src='https://meet.jit.si/external_api.js'></script>
<script>
    const domain = "meet.jit.si";
    const options = {
        roomName: "{{ $roomName }}",
        width: "100%",
        height: 600,
        parentNode: document.getElementById("jitsi-container"),
        userInfo: {
            displayName: "{{ $displayName }}"
        }
    };
    const api = new JitsiMeetExternalAPI(domain, options);
</script>
@endsection
