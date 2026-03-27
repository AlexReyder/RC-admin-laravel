Давай начнем работу по этапно. Ты будешь отправлять код, я проверять его и затем добавлять. Отправляй код, а не готовые файлы. 
Вот мой репозиторий - https://github.com/AlexReyder/RC-admin-laravel
У нас будет сайт на wordpress и в корневой директории с файлами wordpress у нас будет папка apartments с файлами Laravel, которую мы сейчас будет делать. Когда закончим с Laravel мы настроим apache / nginx для правильного URL.
Laravel path url match:
/appartments - /
/appartments/admin  - /admin
/appartments/admin/login - /admin/login
/appartments/admin/users - /admin/users
Мой стек: Laravel, Livewire, Volt, FluxUI.
Я использовал Laravel Starter Kit. Мы сначала будет разрабатывать панель администратора, а потом перейдем к публичной части с квартирами. Давай начнем с users - здесь мы можем управлять пользователями, добавлять их, удалять. Будут две роли superadmin и admin. 