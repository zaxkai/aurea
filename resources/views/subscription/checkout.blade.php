<!DOCTYPE html>
<html>
<head>
    <title>Upgrade Premium - Aurea</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo_aurea.png') }}">
    <script src="https://app.sandbox.midtrans.com/snap/snap.js"
            data-client-key="{{ config('midtrans.client_key') }}"></script>
</head>
<body>
    <h1>Memproses pembayaran...</h1>
    <script>
        window.snap.pay('{{ $snapToken }}', {
            onSuccess: function(result) {
                window.location.href = '{{ route("subscription.index") }}';
            },
            onPending: function(result) {
                window.location.href = '{{ route("subscription.index") }}';
            },
            onError: function(result) {
                alert('Pembayaran gagal, coba lagi ya');
            }
        });
    </script>
</body>
</html>