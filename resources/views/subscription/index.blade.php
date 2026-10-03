<x-app-layout>
    <div class="p-6">
        @if($activeSubscription)
            <p>Kamu sudah premium sampai {{ $activeSubscription->expires_at->format('d M Y') }}</p>
        @else
            <h2>Upgrade ke Premium Aurea</h2>
            <p>Rp29.000 / bulan</p>
            <form method="POST" action="{{ route('subscription.checkout') }}">
                @csrf
                <button type="submit">Upgrade Sekarang</button>
            </form>
        @endif
    </div>
</x-app-layout>