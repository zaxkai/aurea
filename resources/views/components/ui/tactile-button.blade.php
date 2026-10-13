@props([
    'selected' => false,
])

<div {{ $attributes->only('wire:key') }} class="group w-full p-[1px] rounded-[18px] transition-all duration-300 ease-out {{ $selected ? 'bg-gradient-to-r from-cyan-400 via-teal-300 to-cyan-400 shadow-[0_0_25px_rgba(6,182,212,0.45)]' : 'bg-gradient-to-b from-cyan-500/30 via-neutral-800/20 to-cyan-950/40 hover:from-cyan-400/50 hover:to-cyan-700/40 shadow-xl' }}">
    <button {{ $attributes->except('wire:key')->merge([
        'type' => 'button',
        'class' => 'relative flex items-center justify-center w-full min-h-[50px] py-3.5 px-6 border-0 rounded-[17px] overflow-hidden cursor-pointer transition-all duration-300 ease-out focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#06b6d4] focus-visible:outline-offset-[3px] ' .
        ($selected
            ? 'bg-gradient-to-r from-[#031d2e] via-[#052b42] to-[#031d2e] text-white font-semibold shadow-[0_16px_36px_rgba(6,182,212,0.35),inset_0_0_0_1px_rgba(46,224,224,0.45)] scale-[1.01]'
            : 'bg-[#050b11] text-[#e0faff]/90 font-medium text-[13.5px] sm:text-sm shadow-[0_14px_28px_rgba(4,24,36,0.35),0_3px_8px_rgba(5,10,15,0.4),inset_0_0_0_1px_rgba(255,255,255,0.06)] hover:-translate-y-[2px] hover:shadow-[0_20px_40px_rgba(6,182,212,0.25),0_4px_10px_rgba(5,10,15,0.45)] hover:text-white active:translate-y-[1px] active:scale-[0.985]')
    ]) }}>
        {{-- Subtle ambient fluidic glow --}}
        <span class="absolute inset-0 pointer-events-none opacity-25 group-hover:opacity-40 transition-opacity duration-300 bg-[radial-gradient(circle_at_50%_0%,rgba(6,182,212,0.35),transparent_70%)]" aria-hidden="true"></span>

        {{-- Active glowing status indicator --}}
        @if($selected)
            <span class="absolute left-4 size-2 rounded-full bg-[#06b6d4] shadow-[0_0_10px_#06b6d4] animate-pulse"></span>
        @endif

        {{-- Content --}}
        <span class="relative z-10 pointer-events-none tracking-wide drop-shadow-[0_1px_8px_rgba(0,18,25,0.85)] flex items-center justify-center gap-2">
            {{ $slot }}
        </span>
    </button>
</div>
