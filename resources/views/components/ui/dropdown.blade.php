@props([
    'align' => 'right',
])

@php
    $alignment = [
        'left' => 'left-0 origin-top-left',
        'right' => 'right-0 origin-top-right',
    ];
@endphp

<div
    x-data="{
        open: false,
        openUp: false,
        id: '{{ (string) str()->uuid() }}',
        toggle() {
            if (this.open) {
                this.close();

                return;
            }

            this.$dispatch('admin-dropdown-open', { id: this.id });
            this.open = true;
            this.$nextTick(() => this.updatePosition());
        },
        close() {
            this.open = false;
            this.openUp = false;
        },
        updatePosition() {
            const panel = this.$refs.panel;
            const trigger = this.$refs.trigger;

            if (! panel || ! trigger) {
                return;
            }

            this.openUp = false;

            this.$nextTick(() => {
                const triggerRect = trigger.getBoundingClientRect();
                const panelHeight = panel.offsetHeight;
                const availableBelow = window.innerHeight - triggerRect.bottom;
                const availableAbove = triggerRect.top;

                this.openUp = availableBelow < panelHeight + 16 && availableAbove > availableBelow;
            });
        },
    }"
    x-on:admin-dropdown-open.window="if ($event.detail.id !== id) close()"
    x-on:click.outside="close()"
    x-on:keydown.escape.window="close()"
    x-on:resize.window.debounce.100ms="if (open) updatePosition()"
    {{ $attributes->class('relative') }}
>
    <button
        type="button"
        x-ref="trigger"
        x-on:click="toggle()"
        x-bind:aria-expanded="open ? 'true' : 'false'"
        class="inline-flex"
    >
        <span class="pointer-events-none inline-flex">
            {{ $trigger ?? '' }}
        </span>
    </button>

    <div
        x-cloak
        x-ref="panel"
        x-show="open"
        x-transition.origin.top.duration.120ms
        x-bind:class="openUp ? 'bottom-full mb-2' : 'top-full mt-2'"
        class="absolute z-[80] min-w-48 rounded-[0.75rem] border border-[var(--color-line)] bg-[var(--color-panel)] p-1.5 shadow-[0_20px_48px_rgba(33,27,21,0.12)] {{ $alignment[$align] }}"
    >
        {{ $slot }}
    </div>
</div>
