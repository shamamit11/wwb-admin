@props([
    'colspan' => 1,
    'title' => 'Nothing here yet',
    'message' => null,
])

<tr>
    <td colspan="{{ $colspan }}" class="p-4 sm:p-6">
        <x-ui.empty-state :title="$title" :message="$message" />
    </td>
</tr>
