<?php

use App\Models\Flat;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new
#[Layout('components.layouts.app')]
#[Title('Квартиры')]
class extends Component {
    use WithPagination;

    public string $search = '';
    public string $sortBy = 'id';
    public string $sortDirection = 'desc';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if (! in_array($column, ['id', 'building', 'number', 'floor', 'rooms_number', 'price', 'created_at'], true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function editFlat(int $flatId): void
    {
        Flux::toast(
            heading: 'Подготовлено',
            text: "Редактирование квартиры #{$flatId} подключим следующим шагом.",
            variant: 'success',
        );
    }

    public function hideFlat(int $flatId): void
    {
        Flux::toast(
            heading: 'Подготовлено',
            text: "Скрытие квартиры #{$flatId} подключим следующим шагом.",
            variant: 'success',
        );
    }

    public function deleteFlat(int $flatId): void
    {
        Flux::toast(
            heading: 'Подготовлено',
            text: "Удаление квартиры #{$flatId} подключим следующим шагом.",
            variant: 'danger',
        );
    }

    public function with(): array
    {
        $search = trim($this->search);

        $flats = Flat::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('title', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhere('finish_date', 'like', '%'.$search.'%')
                        ->orWhere('finishing', 'like', '%'.$search.'%')
                        ->orWhere('floor_position', 'like', '%'.$search.'%');

                    if (is_numeric($search)) {
                        $numericSearch = (int) $search;

                        $subQuery
                            ->orWhere('id', $numericSearch)
                            ->orWhere('building', $numericSearch)
                            ->orWhere('number', $numericSearch)
                            ->orWhere('floor', $numericSearch)
                            ->orWhere('rooms_number', $numericSearch)
                            ->orWhere('price', $numericSearch);
                    }
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(15);

        return [
            'flats' => $flats,
        ];
    }
}; ?>

<section class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Квартиры</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Список квартир в системе.
            </p>
        </div>
    </div>

    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div class="min-w-[320px] w-full">
            <flux:input
                wire:model.live.debounce.300ms="search"
                label="Поиск"
                placeholder="ID, корпус, номер, этаж, title"
            />
        </div>
    </div>

    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
        <flux:table :paginate="$flats">
            <flux:table.columns>
                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'building'"
                    :direction="$sortDirection"
                    wire:click="sort('building')"
                >
                    Корпус
                </flux:table.column>

                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'floor'"
                    :direction="$sortDirection"
                    wire:click="sort('floor')"
                >
                    Этаж
                </flux:table.column>

                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'number'"
                    :direction="$sortDirection"
                    wire:click="sort('number')"
                >
                    № квартиры
                </flux:table.column>

                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'rooms_number'"
                    :direction="$sortDirection"
                    wire:click="sort('rooms_number')"
                >
                    Комнат
                </flux:table.column>

                <flux:table.column>
                    Площадь
                </flux:table.column>

                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'price'"
                    :direction="$sortDirection"
                    wire:click="sort('price')"
                >
                    Цена
                </flux:table.column>

                <flux:table.column>
                    Статус
                </flux:table.column>

                <flux:table.column align="end"></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($flats as $flat)
                    <flux:table.row :key="$flat->id">
                        <flux:table.cell>
                            {{ $flat->building }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $flat->floor }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $flat->number }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $flat->rooms_number }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ number_format((float) $flat->square, 2, ',', ' ') }} м²
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ number_format((int) $flat->price, 0, ',', ' ') }} ₽
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($flat->sold)
                                <flux:badge color="rose" size="sm" inset="top bottom">
                                    Продана
                                </flux:badge>
                            @else
                                <flux:badge color="emerald" size="sm" inset="top bottom">
                                    Доступна
                                </flux:badge>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell align="end">
                            <flux:dropdown position="bottom" align="end">
                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    square
                                    icon="ellipsis-vertical"
                                    inset="top bottom"
                                />

                                <flux:menu>
                                    <flux:menu.item
                                        icon="pencil-square"
                                        wire:click="editFlat({{ $flat->id }})"
                                    >
                                        Редактировать
                                    </flux:menu.item>

                                    <flux:menu.item
                                        icon="eye-slash"
                                        wire:click="hideFlat({{ $flat->id }})"
                                    >
                                        Скрыть
                                    </flux:menu.item>

                                    <flux:menu.separator />

                                    <flux:menu.item
                                        variant="danger"
                                        icon="trash"
                                        wire:click="deleteFlat({{ $flat->id }})"
                                    >
                                        Удалить
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="8">
                            Квартиры не найдены.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</section>