<div
    wire:key="toggle-filters-{{ $tableName }}')"
    class="flex mr-2 mt-2 sm:mt-0 gap-3"
>
    <button
        wire:click="toggleFilters"
        type="button"
        class="pg-btn-white
   "
    >
        <x-livewire-powergrid::icons.filter class="h-4 w-4 text-pg-primary-500" />
    </button>
</div>
