<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Flux\Flux;

new
#[Layout('components.layouts.app')]
#[Title('Пользователи')]
class extends Component {
    use WithPagination;

    public string $search = '';
    public string $sortBy = 'name';
    public string $sortDirection = 'asc';

    public bool $showCreateModal = false;
    public bool $showDeleteDialog = false;

    public ?int $deleteUserId = null;
    public ?string $deleteUserName = null;

    public string $name = '';
    public string $email = '';
    public string $role = 'admin';
    public string $password = '';
    public string $password_confirmation = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if (! in_array($column, ['name', 'email', 'created_at'], true)) {
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

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->resetValidation();
        $this->role = $this->availableRoles()[0]->value;
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function createUser(): void
    {
        $validated = $this->validate(
    [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
        'role' => ['required', Rule::in($this->availableRoleValues())],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ],
    [
        'name.required' => 'Введите имя пользователя.',
        'name.max' => 'Имя пользователя не должно превышать 255 символов.',

        'email.required' => 'Введите email.',
        'email.email' => 'Введите корректный email.',
        'email.max' => 'Email не должен превышать 255 символов.',
        'email.unique' => 'Пользователь с таким email уже существует.',

        'role.required' => 'Выберите роль пользователя.',
        'role.in' => 'Выбрана недопустимая роль.',

        'password.required' => 'Введите пароль.',
        'password.min' => 'Пароль должен содержать не менее 8 символов.',
        'password.confirmed' => 'Подтверждение пароля не совпадает.',
    ],
    [
        'name' => 'имя',
        'email' => 'email',
        'role' => 'роль',
        'password' => 'пароль',
    ],
);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        $this->closeCreateModal();

        Flux::toast(
            heading: 'Успешно',
            text: 'Пользователь успешно создан.',
            variant: 'success',
        );
    }

    public function confirmDelete(int $userId): void
    {
        $user = User::findOrFail($userId);

        if (! $this->canManageUser($user)) {
            abort(403);
        }

        if ($user->is(auth()->user())) {
             Flux::toast(
                heading: 'Ошибка',
                text: 'Нельзя удалить текущего пользователя.',
                variant: 'danger',
            );
            return;
        }

        $this->resetErrorBag('delete');

        $this->deleteUserId = $user->id;
        $this->deleteUserName = $user->name;
        $this->showDeleteDialog = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteDialog = false;
        $this->deleteUserId = null;
        $this->deleteUserName = null;
    }

    public function deleteUser(): void
    {
        if (! $this->deleteUserId) {
            return;
        }

        $user = User::findOrFail($this->deleteUserId);

        if (! $this->canManageUser($user)) {
            abort(403);
        }

        if ($user->is(auth()->user())) {
            $this->cancelDelete();
             Flux::toast(
                heading: 'Ошибка',
                text: 'Нельзя удалить текущего пользователя.',
                variant: 'danger',
            );
            return;
        }

        $user->delete();

        $this->cancelDelete();

        Flux::toast(
            heading: 'Успешно',
            text: 'Пользователь удалён.',
            variant: 'success',
        );
    }

    public function with(): array
    {
        $currentUser = auth()->user();

        $users = User::query()
            ->when($currentUser->role === UserRole::ADMIN, function ($query) {
                $query->where('role', UserRole::ADMIN->value);
            })
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->simplePaginate(10);

        return [
            'users' => $users,
            'roles' => $this->availableRoles(),
        ];
    }

    protected function availableRoles(): array
    {
        return auth()->user()->isSuperadmin()
            ? [UserRole::SUPERADMIN, UserRole::ADMIN]
            : [UserRole::ADMIN];
    }

    protected function availableRoleValues(): array
    {
        return array_map(
            static fn (UserRole $role) => $role->value,
            $this->availableRoles(),
        );
    }

    protected function canManageUser(User $user): bool
    {
        if (auth()->user()->isSuperadmin()) {
            return true;
        }

        return $user->role === UserRole::ADMIN;
    }

    protected function resetForm(): void
    {
        $this->name = '';
        $this->email = '';
        $this->role = UserRole::ADMIN->value;
        $this->password = '';
        $this->password_confirmation = '';
    }
}; ?>

<section class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Пользователи</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Управление пользователями системы.
            </p>
        </div>
    </div>

    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div class="min-w-[280px] w-full">
            <flux:input
                wire:model.live.debounce.300ms="search"
                label="Поиск"
                placeholder="Имя или email"
            />
        </div>

        <div class="self-end">
            <flux:button wire:click="openCreateModal" variant="primary">
                Добавить пользователя
            </flux:button>
        </div>
    </div>



    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
        <flux:table :paginate="$users">
            <flux:table.columns>
                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'name'"
                    :direction="$sortDirection"
                    wire:click="sort('name')"
                >
                    Имя
                </flux:table.column>

                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'email'"
                    :direction="$sortDirection"
                    wire:click="sort('email')"
                >
                    Email
                </flux:table.column>

                <flux:table.column>
                    Роль
                </flux:table.column>

                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'created_at'"
                    :direction="$sortDirection"
                    wire:click="sort('created_at')"
                >
                    Создан
                </flux:table.column>

                <flux:table.column align="end"></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($users as $user)
                    <flux:table.row :key="$user->id">
                        <flux:table.cell variant="strong">
                            {{ $user->name }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $user->email }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge
                                size="sm"
                                color="{{ $user->isSuperadmin() ? 'violet' : 'blue' }}"
                                inset="top bottom"
                            >
                                {{ $user->roleLabel() }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $user->created_at?->format('d.m.Y H:i') }}
                        </flux:table.cell>

                        <flux:table.cell align="end">
                            @if (! $user->is(auth()->user()))
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
                                            variant="danger"
                                            icon="trash"
                                            wire:click="confirmDelete({{ $user->id }})"
                                        >
                                            Удалить
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            @else
                                <flux:badge size="sm" color="zinc" inset="top bottom">
                                    Текущий пользователь
                                </flux:badge>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5">
                            Пользователи не найдены.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal wire:model.self="showCreateModal" class="md:w-[32rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Новый пользователь</flux:heading>
                <flux:text class="mt-2">
                    @if (auth()->user()->isSuperadmin())
                        Создание администратора или superadmin.
                    @else
                        Создание администратора.
                    @endif
                </flux:text>
            </div>

            <form wire:submit="createUser" class="grid gap-4 md:grid-cols-2">
                <flux:input
                    wire:model="name"
                    label="Имя"
                    type="text"
                    required
                />

                <flux:input
                    wire:model="email"
                    label="Email"
                    type="email"
                    required
                />

                <flux:select
                    wire:model="role"
                    label="Роль"
                    required
                >
                    @foreach ($roles as $roleItem)
                        <flux:select.option value="{{ $roleItem->value }}">
                            {{ $roleItem->label() }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <div></div>

                <flux:input
                    wire:model="password"
                    label="Пароль"
                    type="password"
                    required
                />

                <flux:input
                    wire:model="password_confirmation"
                    label="Подтверждение пароля"
                    type="password"
                    required
                />

                <div class="md:col-span-2 flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="closeCreateModal">
                        Отмена
                    </flux:button>

                    <flux:button type="submit" variant="primary">
                        Создать пользователя
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal wire:model.self="showDeleteDialog" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Удалить пользователя?</flux:heading>
                <flux:text class="mt-2">
                    Пользователь
                    <span class="font-medium text-zinc-900 dark:text-white">
                        {{ $deleteUserName }}
                    </span>
                    будет удалён без возможности восстановления.
                </flux:text>
            </div>

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="cancelDelete">
                    Отмена
                </flux:button>

                <flux:button type="button" variant="danger" wire:click="deleteUser">
                    Удалить
                </flux:button>
            </div>
        </div>
    </flux:modal>
</section>