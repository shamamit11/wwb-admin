<div {{ $attributes->class('inline-flex flex-wrap items-center gap-1.5 rounded-full border border-[var(--color-line)] bg-[color-mix(in_srgb,var(--color-panel-soft)_70%,white)] p-1.5 shadow-[inset_0_1px_0_rgba(255,255,255,0.7),0_8px_20px_rgba(20,27,43,0.06)]') }} role="tablist">
    {{ $slot }}
</div>
