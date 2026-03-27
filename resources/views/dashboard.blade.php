<x-layouts.app>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Dashboard</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Панель администратора квартир.
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="text-sm text-zinc-500 dark:text-zinc-400">Квартиры</div>
                <div class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">
                    {{ \App\Models\Flat::count() }}
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="text-sm text-zinc-500 dark:text-zinc-400">Пользователи</div>
                <div class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">
                    {{ \App\Models\User::count() }}
                </div>
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-2">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-medium text-zinc-900 dark:text-white">Управление квартирами</h2>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                            Список квартир, поиск, фильтрация и дальнейшее управление.
                        </p>
                    </div>

                    <flux:button :href="route('admin.flats.index')" wire:navigate variant="primary">
                        Перейти к квартирам
                    </flux:button>
                </div>
            </div>

            @if (auth()->user()->isSuperadmin())
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-medium text-zinc-900 dark:text-white">Управление пользователями</h2>
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                Создание, удаление и дальнейшее управление ролями.
                            </p>
                        </div>

                        <flux:button :href="route('admin.users.index')" wire:navigate variant="primary">
                            Перейти к пользователям
                        </flux:button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>