
<?php

return [
    'button'=>[
        'new-entry'=>"Dodaj",
        'save'=>"Zapisz",
        'save_and_close'=>"Zapisz i zamknij",
        'save_and_create'=>"Zapisz i dodaj kolejny",
        'cancel'=>"Anuluj",
        'delete'=>"Usuń",
        'yes'=>"Tak",
        'edit'=>"Edytuj",
    ],
    'editable'=>[
        'checkbox'=>[
            'checked'=>"Tak",
            'unchecked'=>"Nie",
        ],//checkbox.checked
    ],
    'select'=>[
        'placeholder'=>"Wybierz jedną z opcji",
        'init'=>"Wybrano",
        'deselect'=>"Odznacz",
        'selected'=>"Wybierz",
        'no_items'=>"Brak opcji do wyboru", 
    ],
    'table'=>[
        'deleteConfirm'=>"Czy na pewno chcesz usunąć ten rekord?",
    ],
    'message'=>[
        'created'=>"Rekord został utworzony",
        'updated'=>"Rekord został zaktualizowany",
        'deleted'=>"Usunięto",
    ],
    'dashboard' => 'Pulpit',
    '404'       => 'Strona nie znaleziona.',

    'auth'      => [
        'title'           => 'Autoryzacja',
        'username'        => 'Nazwa użytkownika',
        'password'        => 'Hasło',
        'login'           => 'Zaloguj się',
        'logout'          => 'Wyloguj się',
        'wrong-username'  => 'Zła nazwa użytkownika',
        'wrong-password'  => 'lub hasło',
        'since'           => 'Zarejestrowany dnia :date',
    ],

    'model' => [
        'create'  => 'Utwórz rekord w sekcji :title',
        'edit'    => 'Zaktualizuj rekord w sekcji :title',
    ],

    'links' => [
        'index_page' => 'Na stronę',
    ],

    'env_editor' => [
        'title' => 'Edytor ENV',
        'key' => 'Klucz',
        'var' => 'Wartość',
    ],

    'ckeditor' => [
        'upload' => [
            'success' => 'Plik został przesłany: 
- Rozmiar: :size kb 
- szerokość/wysokość: :width x :height',

            'error' => [
                'common' => 'Nie można przesłać pliku.',
                'wrong_extension' => 'Plik ":file" ma nieprawidłowe rozszerzenie.',
                'filesize_limit' => 'Maksymalny dozwolony rozmiar pliku to :size kb.',
                'filesize_limit_m' => 'Maksymalny dozwolony rozmiar pliku to :size Mb.',
                'imagesize_max_limit' => 'Szerokość x Wysokość = :width x :height 
 Maksymalna szerokość x wysokość to: :maxwidth x :maxheight',
                'imagesize_min_limit' => 'Szerokość x Wysokość = :width x :height 
 Minimalna szerokość x wysokość to: :minwidth x :minheight',
            ],
        ],

        'image_browser' => [
            'title' => 'Wstaw obraz z serwera',
            'subtitle' => 'Wybierz obraz do wstawienia',
        ],
    ],

    'table' => [
        'no-action' => 'Brak akcji',
        'deleted_all' => 'Usuń zaznaczone',
        'make-action' => 'Zatwierdź',
    ],

    'attributes' => [
        'name'                  => 'Nazwa',
        'username'              => 'Nazwa użytkownika',
        'email'                 => 'E-Mail',
        'first_name'            => 'Imię',
        'last_name'             => 'Nazwisko',
        'password'              => 'Hasło',
        'password_confirmation' => 'Potwierdzenie hasła',
        'city'                  => 'Miasto',
        'country'               => 'Kraj',
        'address'               => 'Adres',
        'phone'                 => 'Telefon',
        'mobile'                => 'Telefon komórkowy',
        'age'                   => 'Wiek',
        'sex'                   => 'Płeć',
        'gender'                => 'Płeć',
        'day'                   => 'Dzień',
        'month'                 => 'Miesiąc',
        'year'                  => 'Rok',
        'hour'                  => 'Godzina',
        'minute'                => 'Minuta',
        'second'                => 'Sekunda',
        'title'                 => 'Tytuł',
        'content'               => 'Treść',
        'description'           => 'Opis',
        'excerpt'               => 'Fragment',
        'date'                  => 'Data',
        'time'                  => 'Czas',
        'available'             => 'Dostępny',
        'size'                  => 'Rozmiar',
    ],
];
